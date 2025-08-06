<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Form\Constraint\HexadecimalColor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated use Symfony ColorType instead
 * @see https://symfony.com/doc/6.4/reference/forms/types/color.html
 */
final class ColorType extends AbstractType
{
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['attr']['class'] = 'colorpicker-input';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('required', false);
        $resolver->setDefault('constraints', [
            new HexadecimalColor(),
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'rz_color';
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TextType::class;
    }
}
