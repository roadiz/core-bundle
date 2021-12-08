<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;

/**
 * @package RZ\Roadiz\CoreBundle\Doctrine\Event
 */
abstract class FilterNodesSourcesQueryBuilderCriteriaEvent extends QueryBuilderBuildEvent
{
    /**
     * @inheritDoc
     */
    public function __construct(QueryBuilder $queryBuilder, $property, $value, $actualEntityName)
    {
        parent::__construct($queryBuilder, NodesSources::class, $property, $value, $actualEntityName);
    }

    /**
     * @inheritDoc
     */
    public function supports(): bool
    {
        if ($this->actualEntityName === NodesSources::class) {
            return true;
        }

        $reflectionClass = new \ReflectionClass($this->actualEntityName);
        if ($reflectionClass->isSubclassOf(NodesSources::class)) {
            return true;
        }

        return false;
    }
}
