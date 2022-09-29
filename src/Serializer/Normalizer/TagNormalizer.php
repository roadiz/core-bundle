<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;

/**
 * Override Tag default normalization.
 */
final class TagNormalizer extends AbstractPathNormalizer
{
     /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if ($object instanceof Tag && is_array($data)) {
            $data['slug'] = $object->getTagName();

            if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
                $translatedData = $object->getTranslatedTagsByTranslation($context['translation'])->first() ?: null;
                if ($translatedData instanceof TagTranslation) {
                    $data['name'] = $translatedData->getName();
                    $data['description'] = $translatedData->getDescription();
                    $data['documents'] = $translatedData->getDocuments();
                }
            }
        }
        return $data;
    }
}
