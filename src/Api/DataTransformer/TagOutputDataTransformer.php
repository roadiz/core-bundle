<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\TagOutput;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;

class TagOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = [])
    {
        if (!$data instanceof Tag) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . Tag::class);
        }
        $output = new TagOutput();
        $output->slug = $data->getTagName();
        $output->color = $data->getColor();
        $output->visible = $data->isVisible();

        if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
            $translatedData = $data->getTranslatedTagsByTranslation($context['translation'])->first() ?: null;
            if ($translatedData instanceof TagTranslation) {
                $output->name = $translatedData->getName();
                $output->description = $translatedData->getDescription();
            }
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return TagOutput::class === $to && $data instanceof Tag;
    }
}
