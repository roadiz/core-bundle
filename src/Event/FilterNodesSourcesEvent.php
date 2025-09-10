<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterNodesSourcesEvent extends Event
{
    public function __construct(protected readonly NodesSources $nodeSource)
    {
    }

    public function getNodeSource(): NodesSources
    {
        return $this->nodeSource;
    }
}
