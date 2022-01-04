<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class NodeOutput
{
    /**
     * @var string|null
     * @Groups({"tag", "attribute", "node"})
     */
    public ?string $nodeName = null;
    /**
     * @var bool
     * @Groups({"nodes_sources", "nodes_sources_base", "node"})
     */
    public bool $visible = false;
    /**
     * @var array
     * @Groups({"nodes_sources", "nodes_sources_base", "node"})
     */
    public array $tags = [];
}
