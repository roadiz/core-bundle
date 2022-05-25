<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use RZ\Roadiz\CoreBundle\Entity\RealmNode;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<RealmNode>
 */
final class RealmNodeRepository extends EntityRepository
{
    public function findByNodeIdsAndRealmId(array $nodeIds, int $realmId): array
    {
        $nodeIds = array_filter($nodeIds);
        if (empty($nodeIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('rn');
        $qb->andWhere($qb->expr()->in('rn.node', ':nodeIds'))
            ->andWhere($qb->expr()->eq('rn.realm', ':realmId'))
            ->setParameter('nodeIds', $nodeIds)
            ->setParameter('realmId', $realmId);

        return $qb->getQuery()->getResult();
    }
}
