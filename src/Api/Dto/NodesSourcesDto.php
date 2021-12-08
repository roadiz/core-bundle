<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Serializer\Annotation as Serializer;

abstract class NodesSourcesDto
{
    public string $title = '';
    public string $metaTitle = '';
    public string $metaDescription = '';
    public string $slug = '';
    public ?\DateTime $publishedAt = null;
    public ?Node $node = null;
    public ?Translation $translation = null;
    /**
     * @var string|null
     * @Serializer\MaxDepth(4)
     */
    public ?string $url = null;
}
