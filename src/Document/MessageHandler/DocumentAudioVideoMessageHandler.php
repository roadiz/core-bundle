<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;

/**
 * Detect Audio and Video files metadata using https://github.com/JamesHeinrich/getID3 lib
 * @see https://github.com/JamesHeinrich/getID3
 */
final class DocumentAudioVideoMessageHandler extends AbstractLockingDocumentMessageHandler
{
    /**
     * @param  DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && ($document->isVideo() || $document->isAudio());
    }

    protected function processMessage(AbstractDocumentMessage $message, Document $document): void
    {
        if (!\class_exists('getID3')) {
            return;
        }

        $documentPath = $this->packages->getDocumentFilePath($document);
        $id3 = new \getID3();
        $fileInfo = $id3->analyze($documentPath);

        if (isset($fileInfo['video'])) {
            if (isset($fileInfo['video']['resolution_x'])) {
                $document->setImageWidth($fileInfo['video']['resolution_x']);
            }
            if (isset($fileInfo['video']['resolution_y'])) {
                $document->setImageHeight($fileInfo['video']['resolution_y']);
            }
        }
        if (isset($fileInfo['playtime_seconds'])) {
            $document->setMediaDuration((int) floor($fileInfo['playtime_seconds']));
        }
    }
}
