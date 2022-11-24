<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;
use RZ\Roadiz\Documents\Models\TimeableInterface;

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

    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        if (!\class_exists('getID3')) {
            return;
        }

        /*
         * This process requires document files to be locally stored!
         */
        $videoPath = \tempnam(\sys_get_temp_dir(), 'video_');
        \rename($videoPath, $videoPath .= $document->getFilename());

        /*
        * Copy AV locally
        */
        $videoPathResource = \fopen($videoPath, 'w');
        \stream_copy_to_stream($this->documentsStorage->readStream($document->getMountPath()), $videoPathResource);
        \fclose($videoPathResource);

        $id3 = new \getID3();
        $fileInfo = $id3->analyze($videoPath);

        if ($document instanceof SizeableInterface && isset($fileInfo['video'])) {
            if (isset($fileInfo['video']['resolution_x'])) {
                $document->setImageWidth($fileInfo['video']['resolution_x']);
            }
            if (isset($fileInfo['video']['resolution_y'])) {
                $document->setImageHeight($fileInfo['video']['resolution_y']);
            }
        }
        if ($document instanceof TimeableInterface && isset($fileInfo['playtime_seconds'])) {
            $document->setMediaDuration((int) floor($fileInfo['playtime_seconds']));
        }

        \unlink($videoPath);
    }
}
