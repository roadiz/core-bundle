<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

/*
 * UpdateNodeTypeSchemaMessage must be handled synchronous
 */
final class UpdateNodeTypeSchemaMessage
{
    public function __construct(private readonly int|string|null $nodeTypeId)
    {
    }

    /**
     * @return int|string|null
     */
    public function getNodeTypeId(): int|string|null
    {
        return $this->nodeTypeId;
    }
}
