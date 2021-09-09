<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Solarium\Client;
use Solarium\QueryType\Update\Query\Query;

/**
 * Wrap a Solarium and a DocumentTranslation together to ease indexing.
 *
 * @package RZ\Roadiz\CoreBundle\SearchEngine
 */
class SolariumDocumentTranslation extends AbstractSolarium
{
    public const DOCUMENT_TYPE = 'DocumentTranslation';
    public const IDENTIFIER_KEY = 'document_translation_id_i';

    protected ?DocumentInterface $rzDocument = null;
    protected ?DocumentTranslation $documentTranslation = null;

    /**
     * @param DocumentTranslation    $documentTranslation
     * @param ClientRegistry $clientRegistry
     * @param LoggerInterface   $logger
     * @param MarkdownInterface $markdown
     */
    public function __construct(
        DocumentTranslation $documentTranslation,
        ClientRegistry $clientRegistry,
        LoggerInterface $logger,
        MarkdownInterface $markdown
    ) {
        parent::__construct($clientRegistry, $logger, $markdown);

        $this->documentTranslation = $documentTranslation;
        $this->rzDocument = $documentTranslation->getDocument();
    }

    public function getDocumentId()
    {
        return $this->documentTranslation->getId();
    }

    /**
     * Get a key/value array representation of current node-source document.
     * @return array
     * @throws \Exception
     */
    public function getFieldsAssoc(): array
    {
        $assoc = [];
        $collection = [];

        // Need a documentType field
        $assoc[static::TYPE_DISCRIMINATOR] = static::DOCUMENT_TYPE;
        // Need a nodeSourceId field
        $assoc[static::IDENTIFIER_KEY] = $this->documentTranslation->getId();
        if ($this->rzDocument instanceof Document) {
            $assoc['document_id_i'] = $this->rzDocument->getId();
            $assoc['created_at_dt'] = $this->rzDocument->getCreatedAt()
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s\Z');
            ;
            $assoc['updated_at_dt'] = $this->rzDocument->getUpdatedAt()
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s\Z');
            ;
        }
        $assoc['filename_s'] = $this->rzDocument->getFilename();
        $assoc['mime_type_s'] = $this->rzDocument->getMimeType();

        $translation = $this->documentTranslation->getTranslation();
        $locale = $translation->getLocale();
        $assoc['locale_s'] = $locale;
        $lang = \Locale::getPrimaryLanguage($locale);

        /*
         * Use locale to create field name
         * with right language
         */
        $suffix = '_t';
        if (in_array($lang, static::$availableLocalizedTextFields)) {
            $suffix = '_txt_' . $lang;
        }

        $assoc['title'] = $this->documentTranslation->getName();
        $assoc['title'.$suffix] = $this->documentTranslation->getName();

        /*
         * Remove ctrl characters
         */
        $description = $this->cleanTextContent($this->documentTranslation->getDescription());
        $assoc['description' . $suffix] = $description;

        $assoc['copyright' . $suffix] = $this->documentTranslation->getCopyright();

        $collection[] = $assoc['title'];
        $collection[] = $assoc['description' . $suffix];
        $collection[] = $assoc['copyright' . $suffix];

        $folders = $this->rzDocument->getFolders();
        $folderNames = [];
        /** @var Folder $folder */
        foreach ($folders as $folder) {
            if ($fTrans = $folder->getTranslatedFoldersByTranslation($translation)->first()) {
                $folderNames[] = $fTrans->getName();
            }
        }

        if ($this->logger !== null && count($folderNames) > 0) {
            $this->logger->debug('Indexed document.', [
                'document' => (string) $this->rzDocument,
                'locale' => $this->documentTranslation->getTranslation()->getLocale(),
                'folders' => $folderNames,
            ]);
        }

        // Use tags_txt to be compatible with other data types
        $assoc['tags_txt'] = $folderNames;
        // Compile all tags names into a single localized text field.
        $assoc['tags_txt_'.$lang] = implode(' ', $folderNames);

        /*
         * Collect data in a single field
         * for global search
         */
        $assoc['collection_txt'] = $collection;
        // Compile all text content into a single localized text field.
        $assoc['collection_txt_'.$lang] = implode(PHP_EOL, $collection);

        return $assoc;
    }

    /**
     * Remove any document linked to current node-source.
     *
     * @param Query $update
     * @return boolean
     */
    public function clean(Query $update)
    {
        $update->addDeleteQuery(
            static::IDENTIFIER_KEY . ':"' . $this->documentTranslation->getId() . '"' .
            '&' . static::TYPE_DISCRIMINATOR . ':"' . static::DOCUMENT_TYPE . '"' .
            '&locale_s:"' . $this->documentTranslation->getTranslation()->getLocale() . '"'
        );

        return true;
    }
}
