<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\Events\DocumentCreatedEvent;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Document\DocumentFactory;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Utils\Asset\Packages;
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
        Packages $packages
    ) {
        parent::__construct($managerRegistry, $messengerLogger, $packages);
        $this->ffmpegPath = $ffmpegPath;
        $this->documentFactory = $documentFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param  DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && $document->isVideo() && is_string($this->ffmpegPath);
    }

    protected function processMessage(AbstractDocumentMessage $message, Document $document): void
    {
        $documentPath = $this->packages->getDocumentFilePath($document);
        $thumbnailPath = tempnam(sys_get_temp_dir(), 'thumbnail_');
        \rename($thumbnailPath, $thumbnailPath .= '.jpg');

        $process = new Process([$this->ffmpegPath, '-y', '-i', $documentPath, '-vframes', '1', $thumbnailPath]);

        try {
            $process->mustRun();
            $process->wait();

            $thumbnailDocument = $this->documentFactory
                ->setFolder($document->getFolders()->first() ?: null)
                ->setFile(new File($thumbnailPath))
                ->getDocument();
            if ($thumbnailDocument instanceof Document) {
                $thumbnailDocument->setOriginal($document);
                $document->getThumbnails()->add($thumbnailDocument);
                $this->managerRegistry->getManager()->flush();
                $this->eventDispatcher->dispatch(new DocumentCreatedEvent($thumbnailDocument));
            }
        } catch (ProcessFailedException $exception) {
            throw new UnrecoverableMessageHandlingException(
                sprintf(
                    'Cannot extract thumbnail from %s video file : %s',
                    $documentPath,
                    $exception->getMessage()
                ),
            );
        }
    }
}
