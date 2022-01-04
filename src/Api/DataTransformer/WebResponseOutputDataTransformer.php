<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\WebResponseOutput;

final class WebResponseOutputDataTransformer implements WebResponseDataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = []): ?WebResponseInterface
    {
        if (!$data instanceof PersistableInterface) {
            throw new \InvalidArgumentException(
                'Data to transform must be instance of ' .
                PersistableInterface::class
            );
        }
        $output = new WebResponseOutput();
        $output->item = $data;
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return WebResponseInterface::class === $to && $data instanceof PersistableInterface;
    }
}
