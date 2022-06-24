<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Publishable filter is always used to avoid exposing non-published nodes and node-sources.
 */
final class PublishableFilter extends GeneratedEntityFilter
{
    private PreviewResolverInterface $previewResolver;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param RequestStack $requestStack
     * @param PreviewResolverInterface $previewResolver
     * @param string $generatedEntityNamespacePattern
     * @param LoggerInterface|null $logger
     * @param array|null $properties
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        RequestStack $requestStack,
        PreviewResolverInterface $previewResolver,
        string $generatedEntityNamespacePattern = '#^App\\\GeneratedEntity\\\NS(?:[a-zA-Z]+)$#',
        LoggerInterface $logger = null,
        array $properties = null
    ) {
        parent::__construct($managerRegistry, $requestStack, $generatedEntityNamespacePattern, $logger, $properties);

        $this->previewResolver = $previewResolver;
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null/*, array $context = []*/)
    {
        @trigger_error(sprintf('Using "%s::apply()" is deprecated since 2.2. Use "%s::apply()" with the "filters" context key instead.', __CLASS__, AbstractContextAwareFilter::class), \E_USER_DEPRECATED);

        $this->filterProperty('', '', $queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
    }

    /**
     * Passes a property through the filter.
     *
     * @param string $property
     * @param mixed $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param string|null $operationName
     * @throws \Exception
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        /*
         * If we can preview still need to prevent deleted and archived nodes to appear
         */
        if ($this->previewResolver->isPreview()) {
            /*
             * Apply publication filter for NodesSources
             */
            if (
                $resourceClass === NodesSources::class ||
                preg_match($this->getGeneratedEntityNamespacePattern(), $resourceClass) > 0
            ) {
                $alias = QueryBuilderHelper::addJoinOnce(
                    $queryBuilder,
                    $queryNameGenerator,
                    'o',
                    'node',
                    Join::INNER_JOIN
                );
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->lte($alias . '.status', ':status'))
                    ->setParameter(':status', Node::PUBLISHED);
                return;
            }
            /*
             * Apply publication filter for Nodes
             */
            if ($resourceClass === Node::class) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->lte('o.status', ':status'))
                    ->setParameter(':status', Node::PUBLISHED);
                return;
            }
            return;
        }

        /*
         * Apply publication filter for NodesSources
         */
        if (
            $resourceClass === NodesSources::class ||
            preg_match($this->getGeneratedEntityNamespacePattern(), $resourceClass) > 0
        ) {
            $alias = QueryBuilderHelper::addJoinOnce(
                $queryBuilder,
                $queryNameGenerator,
                'o',
                'node',
                Join::INNER_JOIN
            );
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte('o.publishedAt', ':lte_published_at'))
                ->andWhere($queryBuilder->expr()->eq($alias . '.status', ':status'))
                ->setParameter(':lte_published_at', new \DateTime())
                ->setParameter(':status', Node::PUBLISHED);
            return;
        }
        /*
         * Apply publication filter for Nodes
         */
        if ($resourceClass === Node::class) {
            $alias = QueryBuilderHelper::addJoinOnce(
                $queryBuilder,
                $queryNameGenerator,
                'o',
                'nodeSources',
                Join::INNER_JOIN
            );
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte($alias . '.publishedAt', ':lte_published_at'))
                ->andWhere($queryBuilder->expr()->eq('o.status', ':status'))
                ->setParameter(':lte_published_at', new \DateTime())
                ->setParameter(':status', Node::PUBLISHED);
            return;
        }
    }
}
