<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<DocumentTranslation>
 */
class DocumentTranslationRepository extends EntityRepository
{
    /**
     * @param int $id
     * @return DocumentTranslation|null
     */
    public function findOneWithDocument($id)
    {
        $qb = $this->createQueryBuilder('dt');
        $qb->select('dt, d')
            ->innerJoin('dt.document', 'd')
            ->andWhere($qb->expr()->eq('dt.id', ':id'))
            ->setMaxResults(1)
            ->setParameter(':id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
