<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Model\AttributeInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 */
final class AttributeDocumentsRepository extends EntityRepository
{
    /**
     * @param AttributeInterface $attribute
     *
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLatestPosition(AttributeInterface $attribute)
    {
        $qb = $this->createQueryBuilder('ad');
        $qb->select($qb->expr()->max('ad.position'))
            ->andWhere($qb->expr()->eq('ad.attribute', ':attribute'))
            ->setParameter('attribute', $attribute);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
