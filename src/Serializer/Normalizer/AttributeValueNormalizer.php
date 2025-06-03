<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * Override AttributeValue default normalization.
 */
final class AttributeValueNormalizer extends AbstractPathNormalizer
{
    /**
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if ($object instanceof AttributeValue && is_array($data)) {
            $this->stopwatch->start('normalizeAttributeValue', 'serializer');
            /** @var array<string> $serializationGroups */
            $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];

            $data['type'] = $object->getType();
            $data['code'] = $object->getAttribute()->getCode();
            $data['color'] = $object->getAttribute()->getColor();
            $data['weight'] = $object->getAttribute()->getWeight();

            if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
                $translatedData = $object->getAttributeValueTranslation($context['translation']);
                $data['label'] = $object->getAttribute()->getLabelOrCode($context['translation']);
                if (
                    $translatedData instanceof AttributeValueTranslationInterface
                    && null !== $translatedData->getValue()
                ) {
                    $data['value'] = $translatedData->getValue();
                } else {
                    $data['value'] = $object->getAttributeValueDefaultTranslation()?->getValue();
                }
            }

            if ($data['value'] instanceof \DateTimeInterface) {
                $data['value'] = $data['value']->format(\DateTimeInterface::ATOM);
            }

            if (\in_array('attribute_documents', $serializationGroups, true)) {
                $documentsContext = $context;
                $documentsContext['groups'] = ['document_display'];
                $data['documents'] = array_map(fn (DocumentInterface $document) => $this->decorated->normalize($document, $format, $documentsContext), $object->getAttribute()->getDocuments()->toArray());
            }
            $this->stopwatch->stop('normalizeAttributeValue');
        }

        return $data;
    }
}
