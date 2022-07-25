<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Solarium\Core\Query\DocumentInterface;
use Solarium\QueryType\Update\Query\Query;

/**
 * Wrap a Solarium and a Document’ translations together to ease indexing.
 *
 * @package RZ\Roadiz\CoreBundle\SearchEngine
 */
class SolariumDocument extends AbstractSolarium
{
    protected array $documentTranslationItems;

    /**
     * @param Document $rzDocument
     * @param SolariumFactoryInterface $solariumFactory
     * @param ClientRegistry $clientRegistry
     * @param LoggerInterface $searchEngineLogger
     * @param MarkdownInterface $markdown
     */
    public function __construct(
        Document $rzDocument,
        SolariumFactoryInterface $solariumFactory,
        ClientRegistry $clientRegistry,
        LoggerInterface $searchEngineLogger,
        MarkdownInterface $markdown
    ) {
        parent::__construct($clientRegistry, $searchEngineLogger, $markdown);
        $this->documentTranslationItems = [];

        foreach ($rzDocument->getDocumentTranslations() as $documentTranslation) {
            $this->documentTranslationItems[] = $solariumFactory->createWithDocumentTranslation($documentTranslation);
        }
    }

    /**
     * @deprecated
     */
    public function getDocument()
    {
        throw new \RuntimeException('Method getDocument cannot be called for SolariumDocument.');
    }

    /**
     * @return array<\Solarium\QueryType\Update\Query\Document|DocumentInterface> Each document translation Solr document
     */
    public function getDocuments()
    {
        $documents = [];
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documents[] = $documentTranslationItem->getDocument();
        }

        return array_filter($documents);
    }

    public function getDocumentId()
    {
        throw new \RuntimeException('SolariumDocument should not provide any ID');
    }

    /**
     * Get document from Solr index.
     *
     * @return boolean *FALSE* if no document found linked to current Roadiz document.
     */
    public function getDocumentFromIndex()
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->getDocumentFromIndex();
        }

        return true;
    }

    /**
     * @param Query $update
     * @return $this
     */
    public function createEmptyDocument(Query $update)
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->createEmptyDocument($update);
        }
        return $this;
    }

    protected function getFieldsAssoc()
    {
        return [];
    }

    /**
     * @param Query $update
     *
     * @return bool
     */
    public function clean(Query $update)
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->clean($update);
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function indexAndCommit()
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->indexAndCommit();
        }
        return true;
    }

    /**
     * @return \Solarium\Core\Query\Result\Result
     * @throws \Exception
     */
    public function updateAndCommit()
    {
        $last = null;
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $last = $documentTranslationItem->updateAndCommit();
        }

        return $last;
    }

    /**
     * @param Query $update
     *
     * @return bool
     * @throws \Exception
     */
    public function update(Query $update)
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->update($update);
        }
        return true;
    }

    /**
     * @param Query $update
     *
     * @return bool
     */
    public function remove(Query $update)
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->remove($update);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeAndCommit()
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->removeAndCommit();
        }
    }

    /**
     * @inheritdoc
     */
    public function cleanAndCommit()
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->cleanAndCommit();
        }
    }

    /**
     * @inheritdoc
     */
    public function index()
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->index();
        }

        return true;
    }
}
