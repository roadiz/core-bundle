<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<Document>
 */
final class DocumentRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, Document::class, $dispatcher);
    }

    /**
     * Get a document with its translation id.
     *
     * @param int $id
     * @return mixed|null
     */
    public function findOneByDocumentTranslationId($id)
    {
        $qb = $this->createQueryBuilder('d');
        $qb->select('d, dt')
            ->innerJoin('d.documentTranslations', 'dt')
            ->andWhere($qb->expr()->eq('dt.id', ':id'))
            ->setParameter(':id', $id)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Add a folder filtering to queryBuilder.
     *
     * @param array $criteria
     * @param QueryBuilder $qb
     * @param string $prefix
     */
    protected function filterByFolder(array &$criteria, QueryBuilder $qb, $prefix = 'd')
    {
        if (key_exists('folders', $criteria)) {
            /*
             * Do not filter if folder is null
             */
            if (is_null($criteria['folders'])) {
                return;
            }

            if (is_array($criteria['folders']) || $criteria['folders'] instanceof Collection) {
                /*
                 * Do not filter if folder array is empty.
                 */
                if (count($criteria['folders']) === 0) {
                    return;
                }
                if (
                    in_array("folderExclusive", array_keys($criteria))
                    && $criteria["folderExclusive"] === true
                ) {
                    // To get an exclusive folder filter
                    // we need to filter against each folder id
                    // and to inner join with a different alias for each folder
                    // with AND operator
                    foreach ($criteria['folders'] as $index => $folder) {
                        if (null !== $folder && $folder instanceof Folder) {
                            $alias = 'fd' . $index;
                            $qb->innerJoin($prefix . '.folders', $alias);
                            $qb->andWhere($qb->expr()->eq($alias . '.id', $folder->getId()));
                        }
                    }
                    unset($criteria["folderExclusive"]);
                    unset($criteria['folders']);
                } else {
                    $qb->innerJoin(
                        $prefix . '.folders',
                        'fd',
                        'WITH',
                        'fd.id IN (:folders)'
                    );
                }
            } else {
                $qb->innerJoin(
                    $prefix . '.folders',
                    'fd',
                    'WITH',
                    'fd.id = :folders'
                );
            }
        }
    }

    /**
     * Reimplementing findBy features… with extra things
     *
     * * key => array('<=', $value)
     * * key => array('<', $value)
     * * key => array('>=', $value)
     * * key => array('>', $value)
     * * key => array('BETWEEN', $value, $value)
     * * key => array('LIKE', $value)
     * * key => array('NOT IN', $array)
     * * key => 'NOT NULL'
     *
     * You can filter with translations relation, examples:
     *
     * * `translation => $object`
     * * `translation.locale => 'fr_FR'`
     *
     * @param array        $criteria
     * @param QueryBuilder $qb
     */
    protected function filterByCriteria(array &$criteria, QueryBuilder $qb)
    {
        $simpleQB = new SimpleQueryBuilder($qb);
        /*
         * Reimplementing findBy features…
         */
        foreach ($criteria as $key => $value) {
            if ($key == "folders" || $key == "folderExclusive") {
                continue;
            }

            /*
             * compute prefix for
             * filtering node, and sources relation fields
             */
            $prefix = 'd.';

            // Dots are forbidden in field definitions
            $baseKey = $simpleQB->getParameterKey($key);
            /*
             * Search in translation fields
             */
            if (false !== strpos($key, 'translation.')) {
                $prefix = 't.';
                $key = str_replace('translation.', '', $key);
            } elseif (false !== strpos($key, 'documentTranslations.')) {
                /*
                 * Search in translation fields
                 */
                $prefix = 'dt.';
                $key = str_replace('documentTranslations.', '', $key);
            } elseif ($key == 'translation') {
                $prefix = 'dt.';
            }

            $qb->andWhere($simpleQB->buildExpressionWithoutBinding($value, $prefix, $key, $baseKey));
        }
    }

    /**
     * Create a Criteria object from a search pattern and additionnal fields.
     *
     * @param string $pattern Search pattern
     * @param QueryBuilder $qb QueryBuilder to pass
     * @param array $criteria Additionnal criteria
     * @param string $alias SQL query table alias
     *
     * @return QueryBuilder
     */
    protected function createSearchBy(
        $pattern,
        QueryBuilder $qb,
        array &$criteria = [],
        $alias = "obj"
    ) {
        $this->filterByFolder($criteria, $qb, $alias);
        $this->applyFilterByFolder($criteria, $qb);
        $this->classicLikeComparison($pattern, $qb, $alias);

        /*
         * Search in translations
         */
        $qb->leftJoin($alias . '.documentTranslations', 'dt');
        $criteriaFields = [];
        $metadata = $this->_em->getClassMetadata(DocumentTranslation::class);
        $cols = $metadata->getColumnNames();
        foreach ($cols as $col) {
            $field = $metadata->getFieldName($col);
            $type = $metadata->getTypeOfField($field);
            if (in_array($type, $this->searchableTypes)) {
                $criteriaFields[$field] = '%' . strip_tags((string) $pattern) . '%';
            }
        }
        foreach ($criteriaFields as $key => $value) {
            $fullKey = sprintf('LOWER(%s)', 'dt.' . $key);
            $qb->orWhere($qb->expr()->like($fullKey, $qb->expr()->literal($value)));
        }

        $qb = $this->prepareComparisons($criteria, $qb, $alias);

        return $qb;
    }

    /**
     * Bind parameters to generated query.
     *
     * @param array $criteria
     * @param QueryBuilder $qb
     */
    protected function applyFilterByCriteria(array &$criteria, QueryBuilder $qb)
    {
        /*
         * Reimplementing findBy features…
         */
        $simpleQB = new SimpleQueryBuilder($qb);
        foreach ($criteria as $key => $value) {
            if ($key == "folders" || $key == "folderExclusive") {
                continue;
            }
            $simpleQB->bindValue($key, $value);
        }
    }

    /**
     * Bind tag parameter to final query
     *
     * @param array $criteria
     * @param QueryBuilder $qb
     */
    protected function applyFilterByFolder(array &$criteria, QueryBuilder $qb)
    {
        if (key_exists('folders', $criteria)) {
            if ($criteria['folders'] instanceof Folder) {
                $qb->setParameter('folders', $criteria['folders']->getId());
            } elseif (is_array($criteria['folders']) || $criteria['folders'] instanceof Collection) {
                if (count($criteria['folders']) > 0) {
                    $qb->setParameter('folders', $criteria['folders']);
                }
            } elseif (is_integer($criteria['folders'])) {
                $qb->setParameter('folders', (int) $criteria['folders']);
            }
            unset($criteria["folders"]);
        }
    }

    /**
     * Bind translation parameter to final query
     *
     * @param QueryBuilder $qb
     * @param null|TranslationInterface $translation
     */
    protected function applyTranslationByFolder(
        QueryBuilder $qb,
        TranslationInterface $translation = null
    ) {
        if (null !== $translation) {
            $qb->setParameter('translation', $translation);
        }
    }

    /**
     * Create filters according to any translation criteria OR argument.
     *
     * @param array        $criteria
     * @param QueryBuilder $qb
     * @param TranslationInterface|null $translation
     */
    protected function filterByTranslation(&$criteria, QueryBuilder $qb, TranslationInterface $translation = null)
    {
        if (
            isset($criteria['translation']) ||
            isset($criteria['translation.locale']) ||
            isset($criteria['translation.id'])
        ) {
            $qb->leftJoin('d.documentTranslations', 'dt');
            $qb->leftJoin('dt.translation', 't');
        } else {
            if (null !== $translation) {
                /*
                 * With a given translation
                 */
                $qb->leftJoin(
                    'd.documentTranslations',
                    'dt',
                    'WITH',
                    'dt.translation = :translation'
                );
            } else {
                /*
                 * With a null translation, just take the default one optionally
                 * Using left join instead of inner join.
                 */
                $qb->leftJoin('d.documentTranslations', 'dt');
                $qb->leftJoin(
                    'dt.translation',
                    't',
                    'WITH',
                    't.defaultTranslation = true'
                );
            }
        }
    }

    /**
     * This method allows to pre-filter Documents with a given translation.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param integer|null $limit
     * @param integer|null $offset
     * @param TranslationInterface|null $translation
     *
     * @return QueryBuilder
     */
    protected function getContextualQueryWithTranslation(
        array &$criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null,
        TranslationInterface $translation = null
    ) {
        $qb = $this->createQueryBuilder('d');
        $qb->andWhere($qb->expr()->eq('d.raw', ':raw'))
            ->setParameter('raw', false);

        /*
         * Filtering by tag
         */
        $this->filterByTranslation($criteria, $qb, $translation);
        $this->filterByFolder($criteria, $qb);
        $this->filterByCriteria($criteria, $qb);

        // Add ordering
        if (null !== $orderBy) {
            foreach ($orderBy as $key => $value) {
                $qb->addOrderBy('d.' . $key, $value);
            }
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }
        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * This method allows to pre-filter Documents with a given translation.
     *
     * @param array $criteria
     * @param TranslationInterface|null $translation
     *
     * @return QueryBuilder
     */
    protected function getCountContextualQueryWithTranslation(
        array &$criteria,
        TranslationInterface $translation = null
    ) {
        $qb = $this->getContextualQueryWithTranslation($criteria, null, null, null, $translation);
        return $qb->select($qb->expr()->countDistinct('d.id'));
    }

    /**
     * Just like the findBy method but with relational criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param integer|null $limit
     * @param integer|null $offset
     * @param TranslationInterface|null $translation
     *
     * @return array|Paginator
     */
    public function findBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null,
        TranslationInterface $translation = null
    ) {
        $qb = $this->getContextualQueryWithTranslation(
            $criteria,
            $orderBy,
            $limit,
            $offset,
            $translation
        );

        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByFolder($criteria, $qb);
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
     * Just like the findOneBy method but with relational criteria.
     *
     * @param array            $criteria
     * @param array|null       $orderBy
     * @param TranslationInterface|null $translation
     *
     * @return Document|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBy(
        array $criteria,
        array $orderBy = null,
        TranslationInterface $translation = null
    ) {
        $qb = $this->getContextualQueryWithTranslation(
            $criteria,
            $orderBy,
            1,
            0,
            $translation
        );

        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByFolder($criteria, $qb);
        $this->applyFilterByCriteria($criteria, $qb);
        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        return $query->getOneOrNullResult();
    }

    /**
     * Just like the countBy method but with relational criteria.
     *
     * @param array $criteria
     * @param TranslationInterface|null $translation
     *
     * @return int
     */
    public function countBy(
        $criteria,
        TranslationInterface $translation = null
    ) {
        $query = $this->getCountContextualQueryWithTranslation(
            $criteria,
            $translation
        );

        $this->dispatchQueryBuilderEvent($query, $this->getEntityName());
        $this->applyFilterByFolder($criteria, $query);
        $this->applyFilterByCriteria($criteria, $query);

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param NodesSources  $nodeSource
     * @param NodeTypeField $field
     *
     * @return array
     */
    public function findByNodeSourceAndField(
        NodesSources $nodeSource,
        NodeTypeField $field
    ) {
        $qb = $this->createQueryBuilder('d');
        $qb->addSelect('dt')
            ->addSelect('dd')
            ->leftJoin('d.documentTranslations', 'dt', 'WITH', 'dt.translation = :translation')
            ->innerJoin('d.nodesSourcesByFields', 'nsf', 'WITH', 'nsf.nodeSource = :nodeSource')
            ->leftJoin('d.downscaledDocument', 'dd')
            ->andWhere($qb->expr()->eq('nsf.field', ':field'))
            ->andWhere($qb->expr()->eq('d.raw', ':raw'))
            ->addOrderBy('nsf.position', 'ASC')
            ->setParameter('field', $field)
            ->setParameter('nodeSource', $nodeSource)
            ->setParameter('translation', $nodeSource->getTranslation())
            ->setParameter('raw', false)
            ->setCacheable(true);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find documents used as Settings.
     *
     * @return array
     */
    public function findAllSettingDocuments()
    {
        $query = $this->_em->createQuery('
            SELECT d FROM RZ\Roadiz\CoreBundle\Entity\Document d
            WHERE d.id IN (
                SELECT s.value FROM RZ\Roadiz\CoreBundle\Entity\Setting s
                WHERE s.type = :type
            ) AND d.raw = :raw
        ')->setParameter('type', AbstractField::DOCUMENTS_T)
            ->setParameter('raw', false);

        return $query->getResult();
    }

    /**
     * Find all unused document.
     *
     * @return array
     */
    public function findAllUnused()
    {
        return $this->getAllUnusedQueryBuilder()->getQuery()->getResult();
    }

    /**
     * @return QueryBuilder
     */
    public function getAllUnusedQueryBuilder(): QueryBuilder
    {
        $qb1 = $this->createQueryBuilder('d1');
        $qb2 = $this->_em->createQueryBuilder();

        /*
         * Get documents used by settings
         */
        $qb2->select('s.value')
            ->from(Setting::class, 's')
            ->andWhere($qb2->expr()->eq('s.type', ':type'))
            ->andWhere($qb2->expr()->isNotNull('s.value'))
            ->setParameter('type', AbstractField::DOCUMENTS_T);

        $subQuery = $qb2->getQuery();
        $array = $subQuery->getScalarResult();
        $idArray = [];

        foreach ($array as $value) {
            $idArray[] = (int) $value['value'];
        }

        /*
         * Get unused documents
         */
        $qb1->select('d1.id')
            ->leftJoin('d1.nodesSourcesByFields', 'ns')
            ->leftJoin('d1.tagTranslations', 'ttd')
            ->leftJoin('d1.attributeDocuments', 'ad')
            ->andHaving('COUNT(ns.id) = 0')
            ->andHaving('COUNT(ttd.id) = 0')
            ->andHaving('COUNT(ad.id) = 0')
            ->groupBy('d1.id')
            ->andWhere($qb1->expr()->eq('d1.raw', ':raw'))
            ->andWhere($qb1->expr()->isNull('d1.original'));

        if (count($idArray) > 0) {
            $qb1->andWhere($qb1->expr()->notIn(
                'd1.id',
                $idArray
            ));
        }

        $qb = $this->createQueryBuilder('d');
        $qb->andWhere($qb->expr()->in(
            'd.id',
            $qb1->getQuery()->getDQL()
        ))
            ->setParameter('raw', false);

        return $qb;
    }
}
