<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

/** @deprecated nodeTypes will be static in future Roadiz versions */
final readonly class DeleteNodeTypeMessage implements AsyncMessage
{
    public function __construct(private int|string|null $nodeTypeId)
    {
    }

    public function getNodeTypeId(): int|string|null
    {
        return $this->nodeTypeId;
    }
}
