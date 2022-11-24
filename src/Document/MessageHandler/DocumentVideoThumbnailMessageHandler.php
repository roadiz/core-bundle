<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\DocumentFactory;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Events\DocumentCreatedEvent;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;
use RZ\Roadiz\Documents\Packages;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Detect Audio and Video files metadata using https://github.com/JamesHeinrich/getID3 lib
 * @see https://github.com/JamesHeinrich/getID3
 */
final class DocumentVideoThumbnailMessageHandler extends AbstractLockingDocumentMessageHandler
{
    private ?string $ffmpegPath;
    private DocumentFactory $documentFactory;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ?string $ffmpegPath,
        DocumentFactory $documentFactory,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        Packages $packages,
        FilesystemOperator $documentsStorage
    ) {
        parent::__construct($managerRegistry, $messengerLogger, $packages, $documentsStorage);
        $this->ffmpegPath = $ffmpegPath;
        $this->documentFactory = $documentFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && $document->isVideo() && is_string($this->ffmpegPath);
    }

    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        /*
         * This process requires document files to be locally stored!
         */
        $videoPath = \tempnam(\sys_get_temp_dir(), 'video_');
        \rename($videoPath, $videoPath .= $document->getFilename());

        /*
         * Copy video locally
         */
        $videoPathResource = \fopen($videoPath, 'w');
        \stream_copy_to_stream($this->documentsStorage->readStream($document->getMountPath()), $videoPathResource);
        \fclose($videoPathResource);

        $thumbnailPath = \tempnam(\sys_get_temp_dir(), 'thumbnail_');
        \rename($thumbnailPath, $thumbnailPath .= '.jpg');

        $process = new Process([$this->ffmpegPath, '-y', '-i', $videoPath, '-vframes', '1', $thumbnailPath]);

        try {
            $process->mustRun();
            $process->wait();

            \unlink($videoPath);

            $thumbnailDocument = $this->documentFactory
                ->setFolder($document->getFolders()->first() ?: null)
                ->setFile(new File($thumbnailPath))
                ->getDocument();
            if ($thumbnailDocument instanceof HasThumbnailInterface && $document instanceof HasThumbnailInterface) {
                $thumbnailDocument->setOriginal($document);
                $document->getThumbnails()->add($thumbnailDocument);
                $this->managerRegistry->getManager()->flush();
                $this->eventDispatcher->dispatch(new DocumentCreatedEvent($thumbnailDocument));
            }
        } catch (ProcessFailedException $exception) {
            throw new UnrecoverableMessageHandlingException(
                sprintf(
                    'Cannot extract thumbnail from %s video file : %s',
                    $videoPath,
                    $exception->getMessage()
                ),
            );
        }
    }
}
