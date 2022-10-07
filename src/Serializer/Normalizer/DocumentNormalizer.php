<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;

/**
 * Override Document default normalization.
 */
final class DocumentNormalizer extends AbstractPathNormalizer
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
        if ($object instanceof Document && is_array($data)) {
            $data['type'] = $object->getShortType();

            if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
                $translatedData = $object->getDocumentTranslationsByTranslation($context['translation'])->first() ?: null;
                if ($translatedData instanceof DocumentTranslation) {
                    $data['name'] = $translatedData->getName();
                    $data['description'] = $translatedData->getDescription();
                    $data['copyright'] = $translatedData->getCopyright();
                    $data['alt'] = !empty($translatedData->getName()) ? $translatedData->getName() : $object->getFilename();
                    $data['externalUrl'] = $translatedData->getExternalUrl();
                }
            }
        }
        return $data;
    }
}
