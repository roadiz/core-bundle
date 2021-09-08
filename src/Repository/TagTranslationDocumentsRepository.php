<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<TagTranslationDocuments>
 */
final class TagTranslationDocumentsRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, TagTranslationDocuments::class, $dispatcher);
    }
    /**
     * @param TagTranslation $tagTranslation
     *
     * @return integer
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLatestPosition($tagTranslation)
    {
        $query = $this->_em->createQuery('SELECT MAX(ttd.position)
FROM RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments ttd
WHERE ttd.tagTranslation = :tagTranslation')
                    ->setParameter('tagTranslation', $tagTranslation);

        return (int) $query->getSingleScalarResult();
    }
}
