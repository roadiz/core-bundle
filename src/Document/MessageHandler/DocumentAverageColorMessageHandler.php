<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\AverageColorResolver;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;

final class DocumentAverageColorMessageHandler extends AbstractLockingDocumentMessageHandler
{
    public function __construct(
        private readonly ImageManager $imageManager,
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        FilesystemOperator $documentsStorage,
    ) {
        parent::__construct($managerRegistry, $messengerLogger, $documentsStorage);
    }

    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && $document->isProcessable();
    }

    /**
     * @throws \League\Flysystem\FilesystemException
     */
    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        if (!$document instanceof AdvancedDocumentInterface) {
            return;
        }
        $documentStream = $this->documentsStorage->readStream($document->getMountPath());
        try {
            $mediumColor = (new AverageColorResolver())->getAverageColor($this->imageManager->make($documentStream));
            $document->setImageAverageColor($mediumColor);
        } catch (NotReadableException $exception) {
            $this->messengerLogger->warning(
                'Document file is not a readable image.',
                [
                    'path' => $document->getMountPath(),
                    'message' => $exception->getMessage(),
                ]
            );
        }
    }
}
