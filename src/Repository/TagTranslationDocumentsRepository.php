<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Entity\TagTranslation;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments>
 */
class TagTranslationDocumentsRepository extends EntityRepository
{
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
