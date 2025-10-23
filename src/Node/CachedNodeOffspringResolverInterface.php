<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use RZ\Roadiz\CoreBundle\Entity\Node;

interface CachedNodeOffspringResolverInterface extends NodeOffspringResolverInterface
{
    public const CACHE_PREFIX = 'node_offspring_ids_';
    public const CACHE_TAG_PREFIX = 'node_';
    public function purgeOffspringCache(Node $node): void;
}
