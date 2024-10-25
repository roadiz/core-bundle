<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\Query;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

final class QueryNodesSourcesEvent extends QueryEvent
{
    /**
     * @param Query  $query
     * @param class-string $actualEntityName
     */
    public function __construct(Query $query, private readonly string $actualEntityName)
    {
        parent::__construct($query, NodesSources::class);
    }

    /**
     * @return class-string
     */
    public function getActualEntityName(): string
    {
        return $this->actualEntityName;
    }

    /**
     * @return bool
     * @throws \ReflectionException
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
