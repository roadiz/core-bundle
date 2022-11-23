<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\DownscaleImageManager;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Packages;

final class DocumentRawMessageHandler extends AbstractLockingDocumentMessageHandler
{
    private DownscaleImageManager $downscaleImageManager;

    public function __construct(
        DownscaleImageManager $downscaleImageManager,
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger,
        Packages $packages
    ) {
        parent::__construct($managerRegistry, $logger, $packages);
        $this->downscaleImageManager = $downscaleImageManager;
    }

    /**
     * @param  DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && null !== $document->getRelativePath() && $document->isProcessable();
    }

    protected function processMessage(AbstractDocumentMessage $message, Document $document): void
    {
        $this->downscaleImageManager->processAndOverrideDocument($document);
    }
}
