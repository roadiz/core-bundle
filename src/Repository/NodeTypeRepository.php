<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\NodeType>
 */
class NodeTypeRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAll()
    {
        $qb = $this->createQueryBuilder('nt');
        $qb->addSelect('ntf')
            ->leftJoin('nt.fields', 'ntf')
            ->addOrderBy('nt.name', 'ASC')
            ->setCacheable(true);

        return $qb->getQuery()->getResult();
    }
}
