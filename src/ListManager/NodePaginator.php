<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;

/**
 * A paginator class to filter node entities with limit and search.
 *
 * This class add some translation and security filters
 */
class NodePaginator extends Paginator
{
    protected ?TranslationInterface $translation = null;

    /**
     * @return TranslationInterface|null
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @param TranslationInterface|null $translation
     *
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation = null)
    {
        $this->translation = $translation;
        return $this;
    }

    /**
     * Return entities filtered for current page.
     *
     * @param array   $order
     * @param integer $page
     *
     * @return array
     */
    public function findByAtPage(array $order = [], $page = 1)
    {
        if (null !== $this->searchPattern) {
            return $this->searchByAtPage($order, $page);
        } else {
            $repository = $this->getRepository();
            if ($repository instanceof NodeRepository) {
                return $repository->findBy(
                    $this->criteria,
                    $order,
                    $this->getItemsPerPage(),
                    $this->getItemsPerPage() * ($page - 1),
                    $this->getTranslation()
                );
            }
            return $repository->findBy(
                $this->criteria,
                $order,
                $this->getItemsPerPage(),
                $this->getItemsPerPage() * ($page - 1)
            );
        }
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        if (null === $this->totalCount) {
            if (null !== $this->searchPattern) {
                $this->totalCount = $this->getRepository()
                    ->countSearchBy($this->searchPattern, $this->criteria);
            } else {
                $repository = $this->getRepository();
                if ($repository instanceof NodeRepository) {
                    $this->totalCount = $repository->countBy(
                        $this->criteria,
                        $this->getTranslation()
                    );
                }
                $this->totalCount = $repository->countBy(
                    $this->criteria
                );
            }
        }

        return $this->totalCount;
    }
}
