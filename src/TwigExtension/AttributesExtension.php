<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Model\AttributableInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeGroupInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeValueInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationInterface;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

final class AttributesExtension extends AbstractExtension
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_attributes', [$this, 'getAttributeValues']),
            new TwigFunction('node_source_attributes', [$this, 'getNodeSourceAttributeValues']),
            new TwigFunction('node_source_grouped_attributes', [$this, 'getNodeSourceGroupedAttributeValues']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('attributes', [$this, 'getNodeSourceAttributeValues']),
            new TwigFilter('grouped_attributes', [$this, 'getNodeSourceGroupedAttributeValues']),
            new TwigFilter('attribute_label', [$this, 'getAttributeLabelOrCode']),
            new TwigFilter('attribute_group_label', [$this, 'getAttributeGroupLabelOrCode']),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('datetime', [$this, 'isDateTime']),
            new TwigTest('date', [$this, 'isDate']),
            new TwigTest('country', [$this, 'isCountry']),
            new TwigTest('boolean', [$this, 'isBoolean']),
            new TwigTest('choice', [$this, 'isEnum']),
            new TwigTest('enum', [$this, 'isEnum']),
            new TwigTest('number', [$this, 'isNumber']),
            new TwigTest('percent', [$this, 'isPercent']),
        ];
    }

    public function isDateTime(AttributeValueTranslationInterface $attributeValueTranslation): bool
    {
        return $attributeValueTranslation->getAttributeValue()->getAttribute()->isDateTime();
    }

    public function isDate(AttributeValueTranslationInterface $attributeValueTranslation): bool
    {
        return $attributeValueTranslation->getAttributeValue()->getAttribute()->isDate();
    }

    public function isCountry(AttributeValueTranslationInterface $attributeValueTranslation): bool
    {
        return $attributeValueTranslation->getAttributeValue()->getAttribute()->isCountry();
    }

    public function isBoolean(AttributeValueTranslationInterface $attributeValueTranslation): bool
    {
        return $attributeValueTranslation->getAttributeValue()->getAttribute()->isBoolean();
    }

    public function isEnum(AttributeValueTranslationInterface $attributeValueTranslation): bool
    {
        return $attributeValueTranslation->getAttributeValue()->getAttribute()->isEnum();
    }

    public function isPercent(AttributeValueTranslationInterface $attributeValueTranslation): bool
    {
        return $attributeValueTranslation->getAttributeValue()->getAttribute()->isPercent();
    }

    public function isNumber(AttributeValueTranslationInterface $attributeValueTranslation): bool
    {
        return $attributeValueTranslation->getAttributeValue()->getAttribute()->isInteger()
            || $attributeValueTranslation->getAttributeValue()->getAttribute()->isDecimal();
    }

    /**
     * @throws SyntaxError
     */
    public function getAttributeValues(
        ?AttributableInterface $attributable,
        TranslationInterface $translation,
        bool $hideNotTranslated = false,
    ): array {
        if (null === $attributable) {
            throw new SyntaxError('Cannot call get_attributes on NULL');
        }
        if (!$attributable instanceof AttributableInterface) {
            throw new SyntaxError('get_attributes only accepts entities that implement AttributableInterface');
        }
        $attributeValueTranslations = [];

        if ($hideNotTranslated) {
            $attributeValues = $this->entityManager
                ->getRepository(AttributeValue::class)
                ->findByAttributableAndTranslation(
                    $attributable,
                    $translation
                );
        } else {
            /*
             * Do not filter by translation here as we need to
             * fallback attributeValues to defaultTranslation
             * if not filled up.
             */
            $attributeValues = $this->entityManager
                ->getRepository(AttributeValue::class)
                ->findByAttributable(
                    $attributable
                );
        }

        /** @var AttributeValueInterface $attributeValue */
        foreach ($attributeValues as $attributeValue) {
            $attributeValueTranslation = $attributeValue->getAttributeValueTranslation($translation);
            if (null !== $attributeValueTranslation) {
                $attributeValueTranslations[] = $attributeValueTranslation;
            } elseif (false !== $attributeValue->getAttributeValueTranslations()->first()) {
                $attributeValueTranslations[] = $attributeValue->getAttributeValueTranslations()->first();
            }
        }

        return $attributeValueTranslations;
    }

    /**
     * @throws SyntaxError
     */
    public function getNodeSourceAttributeValues(?NodesSources $nodesSources, bool $hideNotTranslated = false): array
    {
        if (null === $nodesSources) {
            throw new SyntaxError('Cannot call node_source_attributes on NULL');
        }

        return $this->getAttributeValues($nodesSources->getNode(), $nodesSources->getTranslation(), $hideNotTranslated);
    }

    /**
     * @throws SyntaxError
     */
    public function getNodeSourceGroupedAttributeValues(?NodesSources $nodesSources, bool $hideNotTranslated = false): array
    {
        $groups = [
            INF => [
                'group' => null,
                'attributeValues' => [],
            ],
        ];
        $attributeValueTranslations = $this->getNodeSourceAttributeValues($nodesSources, $hideNotTranslated);
        /** @var AttributeValueTranslationInterface $attributeValueTranslation */
        foreach ($attributeValueTranslations as $attributeValueTranslation) {
            $group = $attributeValueTranslation->getAttributeValue()->getAttribute()->getGroup();
            if (null !== $group) {
                if (!isset($groups[$group->getCanonicalName()])) {
                    $groups[$group->getCanonicalName()] = [
                        'group' => $group,
                        'attributeValues' => [],
                    ];
                }
                $groups[$group->getCanonicalName()]['attributeValues'][] = $attributeValueTranslation;
            } else {
                $groups[INF]['attributeValues'][] = $attributeValueTranslation;
            }
        }

        return array_filter($groups, function (array $group) {
            return count($group['attributeValues']) > 0;
        });
    }

    public function getAttributeLabelOrCode(mixed $mixed, ?TranslationInterface $translation = null): ?string
    {
        if (null === $mixed) {
            return null;
        }

        if ($mixed instanceof AttributeInterface) {
            return $mixed->getLabelOrCode($translation);
        }
        if ($mixed instanceof AttributeValueInterface) {
            return $mixed->getAttribute()->getLabelOrCode($translation);
        }
        if ($mixed instanceof AttributeValueTranslationInterface) {
            if (null === $translation) {
                $translation = $mixed->getTranslation();
            }

            return $mixed->getAttributeValue()->getAttribute()->getLabelOrCode($translation);
        }

        return null;
    }

    public function getAttributeGroupLabelOrCode(mixed $mixed, ?TranslationInterface $translation = null): ?string
    {
        if (null === $mixed) {
            return null;
        }
        if ($mixed instanceof AttributeGroupInterface) {
            return $mixed->getTranslatedName($translation);
        }
        if ($mixed instanceof AttributeInterface && null !== $mixed->getGroup()) {
            return $mixed->getGroup()->getTranslatedName($translation);
        }
        if ($mixed instanceof AttributeValueInterface && null !== $mixed->getAttribute()->getGroup()) {
            return $mixed->getAttribute()->getGroup()->getTranslatedName($translation);
        }
        if ($mixed instanceof AttributeValueTranslationInterface && null !== $mixed->getAttribute()->getGroup()) {
            if (null === $translation) {
                $translation = $mixed->getTranslation();
            }

            return $mixed->getAttribute()->getGroup()->getTranslatedName($translation);
        }

        return null;
    }
}
