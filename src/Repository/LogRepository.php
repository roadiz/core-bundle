<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Log;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<Log>
 */
final class LogRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, Log::class, $dispatcher);
    }

    /**
     * Find the latest Log with NodesSources.
     *
     * @param int $maxResult
     * @return Paginator
     */
    public function findLatestByNodesSources(int $maxResult = 5): Paginator
    {
        /*
         * We need to split this query in 2 for performance matter.
         *
         * SELECT l1_.id, l1_.datetime, n0_.id
         * FROM log AS l1_
         * INNER JOIN nodes_sources n0_ ON l1_.node_source_id = n0_.id
         * WHERE l1_.id IN (
         *     SELECT MAX(id)
         *     FROM log
         *     GROUP BY node_source_id
         * )
         * ORDER BY l1_.datetime DESC
         * LIMIT 8
         */

        $subQb = $this->createQueryBuilder('slog');
        $subQb->select($subQb->expr()->max('slog.id'))
            ->addGroupBy('slog.nodeSource');

        $qb = $this->createQueryBuilder('log');
        $qb->select('log.id as id')
            ->innerJoin('log.nodeSource', 'ns')
            ->andWhere($qb->expr()->in('log.id', $subQb->getQuery()->getDQL()))
            ->orderBy('log.datetime', 'DESC')
            ->setMaxResults($maxResult)
        ;
        $ids = $qb->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getScalarResult();

        $qb2 = $this->createQueryBuilder('log');
        $qb2->addSelect('ns, n, dbf')
            ->andWhere($qb2->expr()->in('log.id', ':id'))
            ->innerJoin('log.nodeSource', 'ns')
            ->leftJoin('ns.documentsByFields', 'dbf')
            ->innerJoin('ns.node', 'n')
            ->orderBy('log.datetime', 'DESC')
            ->setParameter(':id', array_map(function (array $item) {
                return $item['id'];
            }, $ids));

        return new Paginator($qb2->getQuery(), true);
    }
}
