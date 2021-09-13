<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\Document\DocumentFinderInterface;

final class DocumentFinder implements DocumentFinderInterface
{
    private ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function findAllByFilenames(array $fileNames)
    {
        return $this->getRepository()->findBy([
            "filename" => $fileNames,
            "raw" => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findOneByFilenames(array $fileNames): ?DocumentInterface
    {
        return $this->getRepository()->findOneBy([
            "filename" => $fileNames,
            "raw" => false,
        ]);
    }

    /**
     * @return DocumentRepository
     */
    protected function getRepository(): DocumentRepository
    {
        return $this->managerRegistry->getRepository(Document::class);
    }
}
