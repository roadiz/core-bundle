<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MultipleEnumerationType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'strict' => true,
            'multiple' => true,
        ]);

        $resolver->setRequired(['nodeTypeField']);
        $resolver->setAllowedTypes('nodeTypeField', [NodeTypeField::class]);

        $resolver->setNormalizer('placeholder', function (Options $options, $placeholder) {
            if ('' !== $options['nodeTypeField']->getPlaceholder()) {
                $placeholder = $options['nodeTypeField']->getPlaceholder();
            }

            return $placeholder;
        });

        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            /** @var NodeTypeField $nodeTypeField */
            $nodeTypeField = $options['nodeTypeField'];
            $values = $nodeTypeField->getDefaultValuesAsArray();

            foreach ($values as $value) {
                $value = trim($value);
                $choices[$value] = $value;
            }

            return $choices;
        });

        $resolver->setNormalizer('expanded', fn (Options $options, $expanded) => $options['nodeTypeField']->isExpanded());
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'enumeration';
    }
}
