<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Utils\Asset\Packages;

final class DocumentSizeMessageHandler extends AbstractLockingDocumentMessageHandler
{
    private ImageManager $imageManager;

    public function __construct(
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        Packages $packages,
        ImageManager $imageManager
    ) {
        parent::__construct($managerRegistry, $messengerLogger, $packages);
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

    protected function processMessage(AbstractDocumentMessage $message, Document $document): void
    {
        $documentPath = $this->packages->getDocumentFilePath($document);
        try {
            $imageProcess = $this->imageManager->make($documentPath);
            $document->setImageWidth($imageProcess->width());
            $document->setImageHeight($imageProcess->height());
        } catch (NotReadableException $exception) {
            $this->logger->warning(
                'Document file is not a readable image.',
                [
                    'path' => $documentPath,
                    'message' => $exception->getMessage()
                ]
            );
        }
    }
}
