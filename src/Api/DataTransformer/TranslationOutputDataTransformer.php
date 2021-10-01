<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\TranslationOutput;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

class TranslationOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = [])
    {
        $output = new TranslationOutput();
        $output->locale = $data->getPreferredLocale();
        $output->defaultTranslation = $data->isDefaultTranslation();
        $output->available = $data->isAvailable();
        $output->name = $data->getName();
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return TranslationOutput::class === $to && $data instanceof TranslationInterface;
    }
}
