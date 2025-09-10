<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\Query;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterQueryCriteriaEvent extends Event
{
    /**
     * @var string
     */
    protected string $property;
    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var class-string
     */
    protected string $entityClass;
    /**
     * @var Query
     */
    protected Query $query;

    /**
     * @param Query $query
     * @param class-string $entityClass
     * @param string $property
     * @param mixed $value
     */
    public function __construct(Query $query, string $entityClass, string $property, $value)
    {
        $this->entityClass = $entityClass;
        $this->property = $property;
        $this->value = $value;
        $this->query = $query;
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * @param Query $query
     *
     * @return FilterQueryCriteriaEvent
     */
    public function setQuery(Query $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param string $entityClass
     * @return bool
     */
    public function supports($entityClass): bool
    {
        return $this->entityClass === $entityClass;
    }
}
