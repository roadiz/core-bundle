<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\AbstractDocumentFactory;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * Create private documents from UploadedFile.
 *
 * Factory methods do not flush, only persist in order to use it in loops.
 */
class PrivateDocumentFactory extends AbstractDocumentFactory
{
    private ManagerRegistry $managerRegistry;

    public function __construct(
        ManagerRegistry $managerRegistry,
        FilesystemOperator $documentsStorage,
        DocumentFinderInterface $documentFinder,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($documentsStorage, $documentFinder, $logger);
        $this->managerRegistry = $managerRegistry;
    }

    protected function persistDocument(DocumentInterface $document): void
    {
        $this->managerRegistry->getManagerForClass(Document::class)->persist($document);
    }

    protected function createDocument(): DocumentInterface
    {
        $document = new Document();
        $document->setPrivate(true);

        return $document;
    }
}
