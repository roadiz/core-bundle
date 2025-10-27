<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

final class AttributeValueTranslationType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $attributeValueTranslation = $builder->getData();

        if ($attributeValueTranslation instanceof AttributeValueTranslationInterface) {
            $defaultOptions = [
                'required' => false,
                'empty_data' => null,
                'label' => false,
                'constraints' => [
                    new Length([
                        'max' => 254,
                    ]),
                ],
            ];
            match ($attributeValueTranslation->getAttributeValue()->getType()) {
                AttributeInterface::INTEGER_T => $builder->add('value', IntegerType::class, $defaultOptions),
                AttributeInterface::DECIMAL_T => $builder->add('value', NumberType::class, $defaultOptions),
                AttributeInterface::DATE_T => $builder->add('value', DateType::class, array_merge($defaultOptions, [
                    'placeholder' => [
                        'year' => 'year',
                        'month' => 'month',
                        'day' => 'day',
                    ],
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'attr' => [
                        'class' => 'rz-datetime-field',
                    ],
                    'constraints' => [],
                ])),
                AttributeInterface::COLOUR_T => $builder->add('value', ColorType::class, $defaultOptions),
                AttributeInterface::COUNTRY_T => $builder->add('value', CountryType::class, $defaultOptions),
                AttributeInterface::DATETIME_T => $builder->add('value', DateTimeType::class, array_merge($defaultOptions, [
                    'placeholder' => [
                        'hour' => 'hour',
                        'minute' => 'minute',
                    ],
                    'date_widget' => 'single_text',
                    'date_format' => 'yyyy-MM-dd',
                    'attr' => [
                        'class' => 'rz-datetime-field',
                    ],
                    'constraints' => [],
                ])),
                AttributeInterface::BOOLEAN_T => $builder->add('value', CheckboxType::class, $defaultOptions),
                AttributeInterface::ENUM_T => $builder->add('value', ChoiceType::class, array_merge($defaultOptions, [
                    'required' => true,
                    'choices' => $this->getOptions($attributeValueTranslation),
                ])),
                AttributeInterface::EMAIL_T => $builder->add('value', EmailType::class, array_merge($defaultOptions, [
                    'constraints' => [
                        new Email(),
                    ],
                ])),
                default => $builder->add('value', TextType::class, $defaultOptions),
            };
        }
        $builder->add('attributeValue', AttributeValueRealmType::class, [
            'label' => false,
        ]);
    }

    protected function getAttribute(AttributeValueTranslationInterface $attributeValueTranslation): ?AttributeInterface
    {
        return $attributeValueTranslation->getAttributeValue()->getAttribute();
    }

    protected function getOptions(AttributeValueTranslationInterface $attributeValueTranslation): array
    {
        $options = $this->getAttribute($attributeValueTranslation)->getOptions(
            $attributeValueTranslation->getTranslation()
        );
        if (null !== $options) {
            $options = array_combine($options, $options);
        }

        return array_merge([
            'attributes.no_value' => null,
        ], $options ?: []);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => AttributeValueTranslationInterface::class,
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'attribute_value_translation';
    }
}
