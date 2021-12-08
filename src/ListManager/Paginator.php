<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Repository\EntityRepository;
use RZ\Roadiz\CoreBundle\Repository\StatusAwareRepository;

/**
 * A simple paginator class to filter entities with limit and search.
 */
class Paginator
{
    protected int $itemsPerPage;
    protected ?int $itemCount = null;
    /**
     * @var class-string
     */
    protected string $entityName;
    protected array $criteria;
    protected ?string $searchPattern = null;
    protected ObjectManager $em;
    protected ?int $totalCount = null;
    protected bool $displayNotPublishedNodes;
    protected bool $displayAllNodesStatuses;

    /**
     * @param ObjectManager $em
     * @param class-string $entityName
     * @param int $itemPerPages
     * @param array $criteria
     */
    public function __construct(
        ObjectManager $em,
        string $entityName,
        int $itemPerPages = 10,
        array $criteria = []
    ) {
        $this->em = $em;
        $this->entityName = $entityName;
        $this->itemsPerPage = $itemPerPages;
        $this->criteria = $criteria;
        $this->displayNotPublishedNodes = false;
        $this->displayAllNodesStatuses = false;

        if ("" == $this->entityName) {
            throw new \RuntimeException("Entity name could not be empty", 1);
        }
        if ($this->itemsPerPage < 1) {
            throw new \RuntimeException("Items par page could not be lesser than 1.", 1);
        }
    }

    /**
     * @return bool
     */
    public function isDisplayingNotPublishedNodes(): bool
    {
        return $this->displayNotPublishedNodes;
    }

    /**
     * @param bool $displayNonPublishedNodes
     * @return Paginator
     */
    public function setDisplayingNotPublishedNodes(bool $displayNonPublishedNodes)
    {
        $this->displayNotPublishedNodes = $displayNonPublishedNodes;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayingAllNodesStatuses(): bool
    {
        return $this->displayAllNodesStatuses;
    }

    /**
     * Switch repository to disable any security on Node status. To use ONLY in order to
     * view deleted and archived nodes.
     *
     * @param bool $displayAllNodesStatuses
     * @return $this
     */
    public function setDisplayingAllNodesStatuses(bool $displayAllNodesStatuses)
    {
        $this->displayAllNodesStatuses = $displayAllNodesStatuses;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchPattern()
    {
        return $this->searchPattern;
    }

    /**
     * @param string $searchPattern
     *
     * @return $this
     */
    public function setSearchPattern($searchPattern)
    {
        $this->searchPattern = $searchPattern;

        return $this;
    }

    /**
     * Return total entities count for given criteria.
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        if (null === $this->totalCount) {
            $repository = $this->getRepository();
            if ($repository instanceof EntityRepository) {
                if (null !== $this->searchPattern) {
                    $this->totalCount = $repository->countSearchBy($this->searchPattern, $this->criteria);
                } else {
                    $this->totalCount = $repository->countBy($this->criteria);
                }
            } else {
                throw new \RuntimeException('Count-by feature is not available using Doctrine default repository.');
            }
        }

        return $this->totalCount;
    }

    /**
     * Return page count according to criteria.
     *
     * **Warning** : EntityRepository must implements *countBy* method
     *
     * @return int
     */
    public function getPageCount(): int
    {
        return (int) ceil($this->getTotalCount() / $this->getItemsPerPage());
    }

    /**
     * Return entities filtered for current page.
     *
     * @param array   $order
     * @param int $page
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findByAtPage(array $order = [], int $page = 1)
    {
        if (null !== $this->searchPattern) {
            return $this->searchByAtPage($order, $page);
        } else {
            return $this->getRepository()
                ->findBy(
                    $this->criteria,
                    $order,
                    $this->getItemsPerPage(),
                    $this->getItemsPerPage() * ($page - 1)
                );
        }
    }

    /**
     * Use a search query to paginate instead of a findBy.
     *
     * @param array   $order
     * @param int $page
     *
     * @return array
     */
    public function searchByAtPage(array $order = [], int $page = 1)
    {
        $repository = $this->getRepository();
        if ($repository instanceof EntityRepository) {
            return $repository->searchBy(
                $this->searchPattern,
                $this->criteria,
                $order,
                $this->getItemsPerPage(),
                $this->getItemsPerPage() * ($page - 1)
            );
        }

        throw new \RuntimeException('Search feature is not available using Doctrine default repository.');
    }

    /**
     * @param int $itemsPerPage
     *
     * @return $this
     */
    public function setItemsPerPage(int $itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }
    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository|EntityRepository|StatusAwareRepository
     */
    protected function getRepository()
    {
        $repository = $this->em->getRepository($this->entityName);
        if ($repository instanceof StatusAwareRepository) {
            $repository->setDisplayingNotPublishedNodes($this->isDisplayingNotPublishedNodes());
            $repository->setDisplayingAllNodesStatuses($this->isDisplayingAllNodesStatuses());
        }
        return $repository;
    }
}
