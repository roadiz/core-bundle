<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;

final class DocumentSizeMessageHandler extends AbstractLockingDocumentMessageHandler
{
    private ImageManager $imageManager;

    public function __construct(
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        FilesystemOperator $documentsStorage,
        ImageManager $imageManager
    ) {
        parent::__construct($managerRegistry, $messengerLogger, $documentsStorage);
        $this->imageManager = $imageManager;
    }

    /**
     * @param  DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && $document->isImage();
    }

    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        if (!$document instanceof SizeableInterface) {
            return;
        }
        try {
            $imageProcess = $this->imageManager->make($this->documentsStorage->readStream($document->getMountPath()));
            $document->setImageWidth($imageProcess->width());
            $document->setImageHeight($imageProcess->height());
        } catch (NotReadableException $exception) {
            $this->messengerLogger->warning(
                'Document file is not a readable image.',
                [
                    'path' => $document->getMountPath(),
                    'message' => $exception->getMessage()
                ]
            );
        }
    }
}
