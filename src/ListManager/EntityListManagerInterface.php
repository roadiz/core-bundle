<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

interface EntityListManagerInterface
{
    public const ITEM_PER_PAGE = 20;

    /**
     * @return $this
     */
    public function setAllowRequestSorting(bool $allowRequestSorting): self;

    /**
     * @return $this
     */
    public function setAllowRequestSearching(bool $allowRequestSearching): self;

    public function isDisplayingNotPublishedNodes(): bool;

    public function setDisplayingNotPublishedNodes(bool $displayNotPublishedNodes): self;

    public function isDisplayingAllNodesStatuses(): bool;

    /**
     * Switch repository to disable any security on Node status. To use ONLY in order to
     * view deleted and archived nodes.
     */
    public function setDisplayingAllNodesStatuses(bool $displayAllNodesStatuses): self;

    /**
     * Handle request to find filter to apply to entity listing.
     *
     * @param bool $disabled Disable pagination and filtering over GET params
     */
    public function handle(bool $disabled = false): void;

    /**
     * Configure a custom current page.
     */
    public function setPage(int $page): self;

    public function disablePagination(): self;

    /**
     * Get Twig assignation to render list details.
     *
     * ** Fields:
     *
     * * description [string]
     * * search [string]
     * * currentPage [int]
     * * pageCount [int]
     * * itemPerPage [int]
     * * itemCount [int]
     * * previousPage [int]
     * * nextPage [int]
     * * nextPageQuery [string]
     * * previousPageQuery [string]
     * * previousQueryArray [array]
     * * nextQueryArray [array]
     */
    public function getAssignation(): array;

    public function getItemCount(): int;

    public function getPageCount(): int;

    /**
     * Return filtered entities.
     */
    public function getEntities(): array;

    /**
     * Configure a custom item count per page.
     */
    public function setItemPerPage(int $itemPerPage): self;
}
