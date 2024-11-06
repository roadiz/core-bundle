<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\Request;

#[Exclude]
class QueryBuilderListManager extends AbstractEntityListManager
{
    protected ?Paginator $paginator = null;
    /**
     * @var callable|null
     */
    protected $searchingCallable;

    public function __construct(
        ?Request $request,
        protected readonly QueryBuilder $queryBuilder,
        protected readonly string $identifier = 'obj',
        protected readonly bool $debug = false,
    ) {
        parent::__construct($request);
    }

    public function setSearchingCallable(?callable $searchingCallable): QueryBuilderListManager
    {
        $this->searchingCallable = $searchingCallable;

        return $this;
    }

    protected function handleSearchParam(string $search): void
    {
        parent::handleSearchParam($search);

        if (\is_callable($this->searchingCallable)) {
            \call_user_func($this->searchingCallable, $this->queryBuilder, $search);
        }
    }

    public function handle(bool $disabled = false): void
    {
        $this->handleRequestQuery($disabled);
    }

    protected function handleOrderingParam(string $field, string $ordering): void
    {
        $this->validateOrderingFieldName($field);
        $this->queryBuilder->addOrderBy(
            sprintf('%s.%s', $this->identifier, $field),
            $ordering
        );
    }

    protected function getPaginator(): Paginator
    {
        if (null === $this->paginator) {
            $this->paginator = new Paginator($this->queryBuilder);
        }

        return $this->paginator;
    }

    public function setPage(int $page): self
    {
        parent::setPage($page);
        $this->queryBuilder->setFirstResult($this->getItemPerPage() * ($page - 1));

        return $this;
    }

    public function setItemPerPage(int $itemPerPage): self
    {
        parent::setItemPerPage($itemPerPage);
        $this->queryBuilder->setMaxResults((int) $itemPerPage);

        return $this;
    }

    public function getItemCount(): int
    {
        return $this->getPaginator()->count();
    }

    public function getEntities(): array
    {
        return $this->getPaginator()->getIterator()->getArrayCopy();
    }

    public function getAssignation(): array
    {
        if ($this->debug) {
            return array_merge(parent::getAssignation(), [
                'dql_query' => $this->queryBuilder->getDQL(),
            ]);
        }

        return parent::getAssignation();
    }
}
