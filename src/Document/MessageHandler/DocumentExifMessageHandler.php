<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;

final class DocumentExifMessageHandler extends AbstractLockingDocumentMessageHandler
{
    /**
     * @param  DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        if (!$document->isLocal()) {
            return false;
        }

        if ($document->getEmbedPlatform() !== "") {
            return false;
        }

        if ($document->getMimeType() == 'image/jpeg' || $document->getMimeType() == 'image/tiff') {
            return true;
        }

        return false;
    }

    protected function processMessage(AbstractDocumentMessage $message, Document $document): void
    {
        if (
            function_exists('exif_read_data') &&
            $document->getDocumentTranslations()->count() === 0
        ) {
            $filePath = $this->packages->getDocumentFilePath($document);
            $exif = @exif_read_data($filePath, 'FILE,COMPUTED,ANY_TAG,EXIF,COMMENT');

            if (false !== $exif) {
                $copyright = $this->getCopyright($exif);
                $description = $this->getDescription($exif);

                if (null !== $copyright || null !== $description) {
                    $this->logger->debug(
                        'EXIF information available for document.',
                        [
                            'document' => (string)$document
                        ]
                    );
                    $manager = $this->managerRegistry->getManagerForClass(DocumentTranslation::class);
                    $defaultTranslation = $this->managerRegistry
                        ->getRepository(Translation::class)
                        ->findDefault();

                    $documentTranslation = new DocumentTranslation();
                    $documentTranslation->setCopyright($copyright)
                        ->setDocument($document)
                        ->setDescription($description)
                        ->setTranslation($defaultTranslation);

                    $manager->persist($documentTranslation);
                }
            }
        }
    }

    /**
     * @param  array $exif
     * @return string|null
     */
    private function getCopyright(array $exif): ?string
    {
        foreach ($exif as $key => $section) {
            if (is_array($section)) {
                foreach ($section as $skey => $value) {
                    if (\mb_strtolower($skey) === 'copyright') {
                        return $value;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param  array $exif
     * @return string|null
     */
    private function getDescription(array $exif): ?string
    {
        foreach ($exif as $key => $section) {
            if (is_string($section) && \mb_strtolower($key) === 'imagedescription') {
                return $section;
            } elseif (is_array($section)) {
                if (\mb_strtolower($key) == 'comment') {
                    $comment = '';
                    foreach ($section as $value) {
                        $comment .= $value . PHP_EOL;
                    }
                    return $comment;
                } else {
                    foreach ($section as $skey => $value) {
                        if (\mb_strtolower($skey) == 'comment') {
                            return $value;
                        }
                    }
                }
            }
        }

        return null;
    }
}
