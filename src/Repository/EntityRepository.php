<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderApplyEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderSelectEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @template TEntityClass of object
 * @extends ServiceEntityRepository<TEntityClass>
 */
abstract class EntityRepository extends ServiceEntityRepository
{
    protected EventDispatcherInterface $dispatcher;

    /**
     * @param ManagerRegistry $registry
     * @param string $entityClass
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, $entityClass);
        $this->dispatcher = $dispatcher;
    }

    /**
     * Alias for DQL and Query builder representing Node relation.
     */
    public const DEFAULT_ALIAS = 'obj';

    /**
     * Alias for DQL and Query builder representing Node relation.
     */
    public const NODE_ALIAS = 'n';

    /**
     * Alias for DQL and Query builder representing NodesSources relation.
     */
    public const NODESSOURCES_ALIAS = 'ns';

    /**
     * Alias for DQL and Query builder representing Translation relation.
     */
    public const TRANSLATION_ALIAS = 't';

    /**
     * Alias for DQL and Query builder representing Tag relation.
     */
    public const TAG_ALIAS = 'tg';

    /**
     * Alias for DQL and Query builder representing NodeType relation.
     */
    public const NODETYPE_ALIAS = 'nt';

    /**
     * Doctrine column types that can be search
     * with LIKE feature.
     *
     * @var array
     */
    protected array $searchableTypes = ['string', 'text'];

    /**
     * @param QueryBuilder $qb
     * @param string $entityClass
     */
    protected function dispatchQueryBuilderEvent(QueryBuilder $qb, $entityClass)
    {
        $this->dispatcher->dispatch(new QueryBuilderSelectEvent($qb, $entityClass));
    }

    /**
     * @param QueryBuilder $qb
     * @param string $property
     * @param mixed $value
     *
     * @return object|QueryBuilderBuildEvent
     */
    protected function dispatchQueryBuilderBuildEvent(QueryBuilder $qb, $property, $value)
    {
        return $this->dispatcher->dispatch(new QueryBuilderBuildEvent(
            $qb,
            $this->getEntityName(),
            $property,
            $value,
            $this->getEntityName()
        ));
    }

    /**
     * @param Query $query
     *
     * @return object|QueryEvent
     */
    protected function dispatchQueryEvent(Query $query)
    {
        return $this->dispatcher->dispatch(new QueryEvent(
            $query,
            $this->getEntityName()
        ));
    }

    /**
     * @param QueryBuilder $qb
     * @param string $property
     * @param mixed $value
     *
     * @return object|QueryBuilderApplyEvent
     */
    protected function dispatchQueryBuilderApplyEvent(QueryBuilder $qb, $property, $value)
    {
        return $this->dispatcher->dispatch(new QueryBuilderApplyEvent(
            $qb,
            $this->getEntityName(),
            $property,
            $value,
            $this->getEntityName()
        ));
    }

    /**
     *
     * @param  array        $criteria
     * @param  QueryBuilder $qb
     * @param  string       $alias
     * @return QueryBuilder
     */
    protected function prepareComparisons(array &$criteria, QueryBuilder $qb, string $alias)
    {
        $simpleQB = new SimpleQueryBuilder($qb);
        foreach ($criteria as $key => $value) {
            /*
             * Main QueryBuilder dispatch loop for
             * custom properties criteria.
             */
            $event = $this->dispatchQueryBuilderBuildEvent($qb, $key, $value);

            if (!$event->isPropagationStopped()) {
                $qb->andWhere($simpleQB->buildExpressionWithoutBinding($value, $alias . '.', $key));
            }
        }

        return $qb;
    }

    /**
     * @param array        $criteria
     * @param QueryBuilder $qb
     */
    protected function applyFilterByCriteria(array &$criteria, QueryBuilder $qb)
    {
        $simpleQB = new SimpleQueryBuilder($qb);
        foreach ($criteria as $key => $value) {
            $event = $this->dispatchQueryBuilderApplyEvent($qb, $key, $value);
            if (!$event->isPropagationStopped()) {
                $simpleQB->bindValue($key, $value);
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string $name
     * @param string $key
     * @param array $value
     *
     * @return Query\Expr\Func
     */
    protected function directExprIn(QueryBuilder $qb, $name, $key, $value)
    {
        $newValue = [];

        if (is_array($value)) {
            foreach ($value as $singleValue) {
                if ($singleValue instanceof PersistableInterface) {
                    $newValue[] = $singleValue->getId();
                } else {
                    $newValue[] = $value;
                }
            }
        }

        return $qb->expr()->in($name, $newValue);
    }

    /**
     * Count entities using a Criteria object or a simple filter array.
     *
     * @param Criteria|mixed|array $criteria or array
     *
     * @return int
     */
    public function countBy($criteria)
    {
        if ($criteria instanceof Criteria) {
            $collection = $this->matching($criteria);
            return $collection->count();
        } elseif (is_array($criteria)) {
            $qb = $this->createQueryBuilder(static::DEFAULT_ALIAS);
            $qb->select($qb->expr()->countDistinct(static::DEFAULT_ALIAS . '.id'));
            $qb = $this->prepareComparisons($criteria, $qb, static::DEFAULT_ALIAS);
            $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
            $this->applyFilterByCriteria($criteria, $qb);

            try {
                return (int) $qb->getQuery()->getSingleScalarResult();
            } catch (NoResultException | NonUniqueResultException $e) {
                return 0;
            }
        }
        return 0;
    }

    /**
     * Create a LIKE comparison with entity texts colunms.
     *
     * @param string $pattern
     * @param QueryBuilder $qb
     * @param string $alias
     */
    protected function classicLikeComparison(
        $pattern,
        QueryBuilder $qb,
        $alias = EntityRepository::DEFAULT_ALIAS
    ) {
        /*
         * Get fields needed for a search query
         */
        $metadata = $this->_em->getClassMetadata($this->getEntityName());
        $criteriaFields = [];
        $cols = $metadata->getColumnNames();
        foreach ($cols as $col) {
            $field = $metadata->getFieldName($col);
            $type = $metadata->getTypeOfField($field);
            if (
                in_array($type, $this->searchableTypes) &&
                $field != 'folder' &&
                $field != 'childrenOrder' &&
                $field != 'childrenOrderDirection'
            ) {
                $criteriaFields[$field] = '%' . strip_tags((string) $pattern) . '%';
            }
        }

        foreach ($criteriaFields as $key => $value) {
            $fullKey = sprintf('LOWER(%s)', $alias . '.' . $key);
            $qb->orWhere($qb->expr()->like($fullKey, $qb->expr()->literal($value)));
        }
    }

    /**
     * Create a Criteria object from a search pattern and additional fields.
     *
     * @param string $pattern Search pattern
     * @param QueryBuilder $qb QueryBuilder to pass
     * @param array $criteria Additional criteria
     * @param string $alias SQL query table alias
     * @return QueryBuilder
     */
    protected function createSearchBy(
        $pattern,
        QueryBuilder $qb,
        array &$criteria = [],
        $alias = EntityRepository::DEFAULT_ALIAS
    ) {
        $this->classicLikeComparison($pattern, $qb, $alias);
        $this->prepareComparisons($criteria, $qb, $alias);

        return $qb;
    }

    /**
     * @param string  $pattern  Search pattern
     * @param array   $criteria Additional criteria
     * @param array   $orders
     * @param int|null $limit
     * @param int|null $offset
     * @param string $alias
     *
     * @return array|Paginator
     * @psalm-return array<TEntityClass>|Paginator<TEntityClass>
     */
    public function searchBy(
        $pattern,
        array $criteria = [],
        array $orders = [],
        $limit = null,
        $offset = null,
        $alias = EntityRepository::DEFAULT_ALIAS
    ) {
        $qb = $this->createQueryBuilder($alias);
        $qb = $this->createSearchBy($pattern, $qb, $criteria, $alias);

        // Add ordering
        foreach ($orders as $key => $value) {
            if (
                strpos($key, static::NODE_ALIAS . '.') !== false &&
                $this->hasJoinedNode($qb, $alias)
            ) {
                $qb->addOrderBy($key, $value);
            } elseif (
                strpos($key, static::NODESSOURCES_ALIAS . '.') !== false &&
                $this->hasJoinedNodesSources($qb, $alias)
            ) {
                $qb->addOrderBy($key, $value);
            } else {
                $qb->addOrderBy($alias . '.' . $key, $value);
            }
        }
        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }
        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByCriteria($criteria, $qb);
        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        if (
            null !== $limit &&
            null !== $offset
        ) {
            /*
             * We need to use Doctrine paginator
             * if a limit is set because of the default inner join
             */
            return new Paginator($query);
        } else {
            return $query->getResult();
        }
    }

    /**
     * @param string $pattern Search pattern
     * @param array $criteria Additional criteria
     * @return int
     */
    public function countSearchBy($pattern, array $criteria = [])
    {
        $qb = $this->createQueryBuilder(static::DEFAULT_ALIAS);
        $qb->select($qb->expr()->countDistinct(static::DEFAULT_ALIAS . '.id'));
        $qb = $this->createSearchBy($pattern, $qb, $criteria);

        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByCriteria($criteria, $qb);

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * @param array $criteria
     * @param QueryBuilder $qb
     * @param string $nodeAlias
     */
    protected function buildTagFiltering(array &$criteria, QueryBuilder $qb, string $nodeAlias = 'n')
    {
        if (key_exists('tags', $criteria)) {
            /*
             * Do not filter if tag is null
             */
            if (is_null($criteria['tags'])) {
                return;
            }

            if (is_array($criteria['tags']) || $criteria['tags'] instanceof Collection) {
                /*
                 * Do not filter if tag array is empty.
                 */
                if (count($criteria['tags']) === 0) {
                    return;
                }
                if (
                    in_array("tagExclusive", array_keys($criteria))
                    && $criteria["tagExclusive"] === true
                ) {
                    // To get an exclusive tag filter
                    // we need to filter against each tag id
                    // and to inner join with a different alias for each tag
                    // with AND operator
                    /**
                     * @var int $index
                     * @var Tag|null $tag Tag can be null if not found
                     */
                    foreach ($criteria['tags'] as $index => $tag) {
                        if (null !== $tag && $tag instanceof Tag) {
                            $alias = static::TAG_ALIAS . $index;
                            $qb->innerJoin($nodeAlias . '.tags', $alias);
                            $qb->andWhere($qb->expr()->eq($alias . '.id', $tag->getId()));
                        }
                    }
                    unset($criteria["tagExclusive"]);
                    unset($criteria['tags']);
                } else {
                    $qb->innerJoin(
                        $nodeAlias . '.tags',
                        static::TAG_ALIAS,
                        'WITH',
                        'tg.id IN (:tags)'
                    );
                }
            } else {
                $qb->innerJoin(
                    $nodeAlias . '.tags',
                    static::TAG_ALIAS,
                    'WITH',
                    'tg.id = :tags'
                );
            }
        }
    }

    /**
     * Bind tag parameters to final query
     *
     * @param array $criteria
     * @param QueryBuilder $qb
     */
    protected function applyFilterByTag(array &$criteria, QueryBuilder $qb)
    {
        if (key_exists('tags', $criteria)) {
            if (null !== $criteria['tags'] && $criteria['tags'] instanceof Tag) {
                $qb->setParameter('tags', $criteria['tags']->getId());
            } elseif (is_array($criteria['tags']) || $criteria['tags'] instanceof Collection) {
                if (count($criteria['tags']) > 0) {
                    $qb->setParameter('tags', $criteria['tags']);
                }
            } elseif (is_integer($criteria['tags'])) {
                $qb->setParameter('tags', (int) $criteria['tags']);
            }
            unset($criteria['tags']);
        }
    }

    /**
     * Ensure that node table is joined only once.
     *
     * @param  QueryBuilder $qb
     * @param  string  $alias
     * @return boolean
     */
    protected function hasJoinedNode(QueryBuilder $qb, string $alias)
    {
        return $this->joinExists($qb, $alias, static::NODE_ALIAS);
    }

    /**
     * Ensure that nodes_sources table is joined only once.
     *
     * @param  QueryBuilder $qb
     * @param  string  $alias
     * @return boolean
     */
    protected function hasJoinedNodesSources(QueryBuilder $qb, string $alias)
    {
        return $this->joinExists($qb, $alias, static::NODESSOURCES_ALIAS);
    }

    /**
     * Ensure that nodes_sources table is joined only once.
     *
     * @param  QueryBuilder $qb
     * @param  string  $alias
     * @return boolean
     */
    protected function hasJoinedNodeType(QueryBuilder $qb, string $alias)
    {
        return $this->joinExists($qb, $alias, static::NODETYPE_ALIAS);
    }

    /**
     * @param QueryBuilder $qb
     * @param string $rootAlias
     * @param string $joinAlias
     * @return bool
     */
    protected function joinExists(QueryBuilder $qb, string $rootAlias, string $joinAlias)
    {
        if (isset($qb->getDQLPart('join')[$rootAlias])) {
            foreach ($qb->getDQLPart('join')[$rootAlias] as $join) {
                if (
                    null !== $join &&
                    $join instanceof Join &&
                    $join->getAlias() === $joinAlias
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
