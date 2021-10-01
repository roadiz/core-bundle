<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class PublishableFilter extends GeneratedEntityFilter
{
    private Security $security;
    private PreviewResolverInterface $previewResolver;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param RequestStack $requestStack
     * @param PreviewResolverInterface $previewResolver
     * @param Security $security
     * @param string $generatedEntityNamespacePattern
     * @param LoggerInterface|null $logger
     * @param array|null $properties
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        RequestStack $requestStack,
        PreviewResolverInterface $previewResolver,
        Security $security,
        string $generatedEntityNamespacePattern = '#^App\\\GeneratedEntity\\\NS(?:[a-zA-Z]+)$#',
        LoggerInterface $logger = null,
        array $properties = null
    ) {
        parent::__construct($managerRegistry, $requestStack, $generatedEntityNamespacePattern, $logger, $properties);

        $this->security = $security;
        $this->previewResolver = $previewResolver;
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
        $canPreview = $this->previewResolver->isPreview() &&
            $this->security->isGranted($this->previewResolver->getRequiredRole());

        /*
         * If we can preview still need to prevent deleted and archived nodes to appear
         */
        if ($canPreview) {
            /*
             * Apply publication filter for NodesSources
             */
            if ($resourceClass === NodesSources::class ||
                preg_match($this->getGeneratedEntityNamespacePattern(), $resourceClass) > 0) {
                $join = $this->addJoinsForNestedProperty('node', 'o', $queryBuilder, $queryNameGenerator);
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->lte($join['alias'] . '.status', ':status'))
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
        if ($resourceClass === NodesSources::class ||
            preg_match($this->getGeneratedEntityNamespacePattern(), $resourceClass) > 0) {
            $join = $this->addJoinsForNestedProperty('node', 'o', $queryBuilder, $queryNameGenerator);
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte('o.publishedAt', ':lte_published_at'))
                ->andWhere($queryBuilder->expr()->eq($join['alias'] . '.status', ':status'))
                ->setParameter(':lte_published_at', new \DateTime())
                ->setParameter(':status', Node::PUBLISHED);
            return;
        }
        /*
         * Apply publication filter for Nodes
         */
        if ($resourceClass === Node::class) {
            $join = $this->addJoinsForNestedProperty('nodeSources', 'o', $queryBuilder, $queryNameGenerator);
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte($join['alias'] . '.publishedAt', ':lte_published_at'))
                ->andWhere($queryBuilder->expr()->eq('o.status', ':status'))
                ->setParameter(':lte_published_at', new \DateTime())
                ->setParameter(':status', Node::PUBLISHED);
            return;
        }
    }
}
