<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\NodeOutput;
use RZ\Roadiz\CoreBundle\Entity\Node;

class NodeOutputDataTransformer implements DataTransformerInterface
{
    private TagOutputDataTransformer $tagOutputDataTransformer;

    /**
     * @param TagOutputDataTransformer $tagOutputDataTransformer
     */
    public function __construct(TagOutputDataTransformer $tagOutputDataTransformer)
    {
        $this->tagOutputDataTransformer = $tagOutputDataTransformer;
    }

    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = [])
    {
        if (!$data instanceof Node) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . Node::class);
        }
        $output = new NodeOutput();
        $output->visible = $data->isVisible();
        $output->tags = $data->getTags()->toArray();
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return NodeOutput::class === $to && $data instanceof Node;
    }
}
