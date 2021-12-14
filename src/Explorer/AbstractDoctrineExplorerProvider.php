<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManager;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package Themes\Rozier\Explorer
 */
abstract class AbstractDoctrineExplorerProvider extends AbstractExplorerProvider
{
    protected ManagerRegistry $managerRegistry;
    protected RequestStack $requestStack;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param RequestStack $requestStack
     */
    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack)
    {
        $this->managerRegistry = $managerRegistry;
        $this->requestStack = $requestStack;
    }

    /**
     * @return string
     */
    abstract protected function getProvidedClassname(): string;

    /**
     * @return array
     */
    abstract protected function getDefaultCriteria(): array;

    /**
     * @return array
     */
    abstract protected function getDefaultOrdering(): array;

    /**
     * @param array $options
     *
     * @return EntityListManagerInterface
     */
    protected function doFetchItems(array $options = []): EntityListManagerInterface
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        $listManager = new EntityListManager(
            $this->requestStack->getCurrentRequest(),
            $this->managerRegistry->getManager(),
            $this->getProvidedClassname(),
            $this->getDefaultCriteria(),
            $this->getDefaultOrdering()
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setItemPerPage($this->options['itemPerPage']);
        $listManager->handle();
        $listManager->setPage((int) $this->options['page']);

        return $listManager;
    }
    /**
     * @inheritDoc
     */
    public function getItems($options = []): array
    {
        $listManager = $this->doFetchItems($options);

        $items = [];
        foreach ($listManager->getEntities() as $entity) {
            $items[] = $this->toExplorerItem($entity);
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getFilters($options = []): array
    {
        $listManager = $this->doFetchItems($options);

        return $listManager->getAssignation();
    }

    /**
     * @inheritDoc
     */
    public function getItemsById($ids = []): array
    {
        if (is_array($ids) && count($ids) > 0) {
            $entities = $this->managerRegistry->getRepository($this->getProvidedClassname())->findBy([
                'id' => $ids
            ]);

            $items = [];
            foreach ($entities as $entity) {
                $items[] = $this->toExplorerItem($entity);
            }

            return $items;
        }

        return [];
    }
}
