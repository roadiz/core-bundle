<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;

interface WebResponseDataTransformerInterface extends DataTransformerInterface
{
    public function transform($object, string $to, array $context = []): ?WebResponseInterface;
}
