<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

trait AttributableTrait
{
    /**
     * @return Collection<AttributeValueInterface>
     */
    public function getAttributeValues(): Collection
    {
        return $this->attributeValues;
    }

    /**
     * @param TranslationInterface $translation
     *
     * @return Collection<AttributeValueInterface>
     */
    public function getAttributesValuesForTranslation(TranslationInterface $translation): Collection
    {
        return $this->getAttributeValues()->filter(function (AttributeValueInterface $attributeValue) use ($translation) {
            /** @var AttributeValueTranslationInterface $attributeValueTranslation */
            foreach ($attributeValue->getAttributeValueTranslations() as $attributeValueTranslation) {
                if ($attributeValueTranslation->getTranslation() === $translation) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * @param TranslationInterface $translation
     *
     * @return Collection<AttributeValueTranslationInterface>
     */
    public function getAttributesValuesTranslations(TranslationInterface $translation): Collection
    {
        /** @var Collection<AttributeValueTranslationInterface> $values */
        $values = $this->getAttributesValuesForTranslation($translation)
            ->map(function (AttributeValueInterface $attributeValue) use ($translation) {
                /** @var AttributeValueTranslationInterface $attributeValueTranslation */
                foreach ($attributeValue->getAttributeValueTranslations() as $attributeValueTranslation) {
                    if ($attributeValueTranslation->getTranslation() === $translation) {
                        return $attributeValueTranslation;
                    }
                }
                return null;
            })
            ->filter(function (?AttributeValueTranslationInterface $attributeValueTranslation) {
                return null !== $attributeValueTranslation;
            })
        ;
        return $values; // phpstan does not understand return type after filtering
    }

    /**
     * @param Collection $attributes
     *
     * @return $this
     */
    public function setAttributeValues(Collection $attributes): static
    {
        $this->attributeValues = $attributes;
        return $this;
    }

    /**
     * @param AttributeValueInterface $attribute
     *
     * @return $this
     */
    public function addAttributeValue(AttributeValueInterface $attribute): static
    {
        if (!$this->getAttributeValues()->contains($attribute)) {
            $this->getAttributeValues()->add($attribute);
        }
        return $this;
    }


    /**
     * @param AttributeValueInterface $attribute
     *
     * @return $this
     */
    public function removeAttributeValue(AttributeValueInterface $attribute): static
    {
        if ($this->getAttributeValues()->contains($attribute)) {
            $this->getAttributeValues()->removeElement($attribute);
        }
        return $this;
    }
}
