<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Controller;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\Archive;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class NodesSourcesArchivesController
{
    private ManagerRegistry $managerRegistry;
    private PreviewResolverInterface $previewResolver;

    public function __construct(ManagerRegistry $managerRegistry, PreviewResolverInterface $previewResolver)
    {
        $this->managerRegistry = $managerRegistry;
        $this->previewResolver = $previewResolver;
    }

    /**
     * Configure your api_resource with an additional collection operation:
     *
     * archives:
     *      method: 'GET'
     *      path: '/articles/archives'
     *      read: false
     *      controller: RZ\Roadiz\CoreBundle\Api\Controller\NodesSourcesArchivesController
     *      pagination_enabled: false
     *      defaults:
     *          resource_date_field: publishedAt
     *      output: RZ\Roadiz\CoreBundle\Api\Dto\Archive
     *      normalization_context:
     *          pagination_enabled: false
     *          groups:
     *              - get
     *              - archives
     *      openapi_context:
     *          summary: Get available XXXXXXX archives
     *          description: |
     *              Get available XXXXXXX archives
     */
    public function __invoke(Request $request): array
    {
        $resourceClass = $request->attributes->get('_api_resource_class');
        $publicationFieldName = $request->attributes->get('resource_date_field', $this->getDefaultPublicationField());
        try {
            $reflection = new \ReflectionClass($resourceClass);
        } catch (\ReflectionException $e) {
            throw new BadRequestHttpException(sprintf(
                '%s resource must be a valid class',
                $resourceClass
            ), $e);
        }
        if ($resourceClass !== NodesSources::class && !$reflection->isSubclassOf(NodesSources::class)) {
            throw new BadRequestHttpException(sprintf(
                '%s resource must be instance of %s',
                $resourceClass,
                NodesSources::class
            ));
        }

        $translation = $this->getTranslation($request);

        return $this->getArchivesCompiledDates($this->getAvailableArchives(
            $resourceClass,
            $translation,
            $publicationFieldName
        ), $publicationFieldName);
    }

    private function getDefaultPublicationField(): string
    {
        return 'publishedAt';
    }

    private function getTranslation(Request $request): TranslationInterface
    {
        $locale = $request->query->get('_locale');
        /** @var TranslationRepository $repository */
        $repository = $this->managerRegistry->getRepository(TranslationInterface::class);
        if (null === $locale) {
            return $repository->findDefault();
        }

        if ($this->previewResolver->isPreview()) {
            $translation = $repository->findOneByLocaleOrOverrideLocale($locale);
        } else {
            $translation = $repository->findOneAvailableByLocaleOrOverrideLocale($locale);
        }

        if (null !== $translation) {
            return $translation;
        }

        throw new BadRequestHttpException(sprintf('“%s” locale is not available', $locale));
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $publicationFieldName
     * @return array<Archive>
     * @throws \Exception
     */
    private function getArchivesCompiledDates(QueryBuilder $queryBuilder, string $publicationFieldName): array
    {
        $entities = [];
        $dates = [];
        $paginator = new Paginator($queryBuilder, false);
        $paginator->setUseOutputWalkers(false);
        /*
         * disable pagination to get all archives
         */
        $paginator->getQuery()->setMaxResults(null);
        $paginator->getQuery()->setFirstResult(null);

        foreach ($paginator as $datetime) {
            $year = $datetime[$publicationFieldName]->format('Y');
            $month = $datetime[$publicationFieldName]->format('Y-m');

            if (!isset($dates[$year])) {
                $dates[$year] = [];
            }
            if (!isset($dates[$month])) {
                $dates[$year][$month] = new \DateTime($datetime[$publicationFieldName]->format('Y-m-01'));
            }
        }

        foreach ($dates as $year => $months) {
            $entity = new Archive();
            $entity->year = $year;
            $entity->months = $months;
            $entities[] = $entity;
        }

        return $entities;
    }

    private function getAvailableArchives(
        string $resourceClass,
        ?TranslationInterface $translation,
        string $publicationFieldName,
        array $criteria = []
    ): QueryBuilder {
        $qb = $this->managerRegistry
            ->getRepository($resourceClass)
            ->createQueryBuilder('p');
        $publicationField = 'p.' . $publicationFieldName;

        $qb->select($publicationField)
            ->innerJoin('p.node', 'n')
            ->andWhere($qb->expr()->lte($publicationField, ':datetime'))
            ->addGroupBy($publicationField)
            ->orderBy($publicationField, 'DESC')
            ->setParameter(':datetime', new \Datetime('now'));

        if (null !== $translation) {
            $qb->andWhere($qb->expr()->eq('p.translation', ':translation'))
                ->setParameter(':translation', $translation);
        }
        /*
         * Enforce post nodes status not to display Archives which are linked to draft posts.
         */
        if ($this->previewResolver->isPreview()) {
            $qb->andWhere($qb->expr()->lte('n.status', Node::PUBLISHED));
        } else {
            $qb->andWhere($qb->expr()->eq('n.status', Node::PUBLISHED));
        }

        if (array_key_exists('node.parent', $criteria) && null !== $criteria['node.parent']) {
            $qb->andWhere($qb->expr()->eq('n.parent', ':parentNode'))
                ->setParameter(':parentNode', $criteria['node.parent']);
        }
        if (array_key_exists('tags', $criteria) && null !== $criteria['tags']) {
            if (array_key_exists('tagExclusive', $criteria) && $criteria['tagExclusive'] === true) {
                /**
                 * @var int $index
                 * @var Tag|string|null $tag Tag can be null if not found
                 */
                foreach ($criteria['tags'] as $index => $tag) {
                    if ($tag instanceof Tag) {
                        $alias = 'tg' . $index;
                        $qb->innerJoin('n.tags', $alias);
                        $qb->andWhere($qb->expr()->eq($alias . '.id', $tag->getId()));
                    } elseif (is_string($tag)) {
                        $alias = 'tg' . $index;
                        $qb->innerJoin('n.tags', $alias);
                        $qb->andWhere($qb->expr()->eq($alias . '.tagName', $tag));
                    }
                }
            } else {
                $qb->innerJoin(
                    'n.tags',
                    'tg',
                    'WITH',
                    'tg.id IN (:tags)'
                )->setParameter(':tags', $criteria['tags']);
            }
        }

        return $qb;
    }
}
