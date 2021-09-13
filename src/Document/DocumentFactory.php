<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\Utils\Asset\Packages;
use RZ\Roadiz\Utils\Document\AbstractDocumentFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Create documents from UploadedFile.
 *
 * Factory methods do not flush, only persist in order to use it in loops.
 *
 * @package RZ\Roadiz\Utils\Document
 */
final class DocumentFactory extends AbstractDocumentFactory
{
    private ManagerRegistry $managerRegistry;

    public function __construct(
        ManagerRegistry $managerRegistry,
        EventDispatcherInterface $dispatcher,
        Packages $packages,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($dispatcher, $packages, $logger);
        $this->managerRegistry = $managerRegistry;
    }

    protected function persistDocument(DocumentInterface $document): void
    {
        $this->managerRegistry->getManagerForClass(Document::class)->persist($document);
    }

    /**
     * @inheritDoc
     */
    protected function createDocument(): DocumentInterface
    {
        return new Document();
    }
}
