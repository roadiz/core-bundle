<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\Document\EventSubscriber\AbstractExifDocumentSubscriber;
use RZ\Roadiz\Utils\Asset\Packages;

final class ExifDocumentSubscriber extends AbstractExifDocumentSubscriber
{
    private ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param Packages $packages
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        Packages $packages,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($packages, $logger);
        $this->managerRegistry = $managerRegistry;
    }

    protected function writeExifData(DocumentInterface $document, string $copyright, string $description): void
    {
        if ($document instanceof Document && $document->getDocumentTranslations()->count() === 0) {
            $manager = $this->managerRegistry->getManagerForClass(DocumentTranslation::class);
            $defaultTranslation = $this->managerRegistry
                ->getRepository(Translation::class)
                ->findDefault();

            $documentTranslation = new DocumentTranslation();
            $documentTranslation->setCopyright($copyright)
                ->setDocument($document)
                ->setDescription($description)
                ->setTranslation($defaultTranslation);

            $manager->persist($documentTranslation);
        }
    }
}
