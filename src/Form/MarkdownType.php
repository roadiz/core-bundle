<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Markdown editor form field type.
 */
class MarkdownType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return TextareaType::class;
    }
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'markdown';
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        /*
         * allow_h2: false
         * allow_h3: false
         * allow_h4: false
         * allow_h5: false
         * allow_h6: false
         * allow_bold: false
         * allow_italic: false
         * allow_blockquote: false
         * allow_list: false
         * allow_nbsp: false
         * allow_nb_hyphen: false
         * allow_image: false
         * allow_return: false
         * allow_link: false
         * allow_hr: false
         * allow_preview: false
         */
        $view->vars['attr']['class'] = 'markdown_textarea';
        $view->vars['attr']['allow_h2'] = $options['allow_h2'];
        $view->vars['attr']['allow_h3'] = $options['allow_h3'];
        $view->vars['attr']['allow_h4'] = $options['allow_h4'];
        $view->vars['attr']['allow_h5'] = $options['allow_h5'];
        $view->vars['attr']['allow_h6'] = $options['allow_h6'];
        $view->vars['attr']['allow_bold'] = $options['allow_bold'];
        $view->vars['attr']['allow_italic'] = $options['allow_italic'];
        $view->vars['attr']['allow_blockquote'] = $options['allow_blockquote'];
        $view->vars['attr']['allow_list'] = $options['allow_list'];
        $view->vars['attr']['allow_nbsp'] = $options['allow_nbsp'];
        $view->vars['attr']['allow_nb_hyphen'] = $options['allow_nb_hyphen'];
        $view->vars['attr']['allow_image'] = $options['allow_image'];
        $view->vars['attr']['allow_return'] = $options['allow_return'];
        $view->vars['attr']['allow_link'] = $options['allow_link'];
        $view->vars['attr']['allow_hr'] = $options['allow_hr'];
        $view->vars['attr']['allow_preview'] = $options['allow_preview'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'allow_h2' => true,
            'allow_h3' => true,
            'allow_h4' => true,
            'allow_h5' => true,
            'allow_h6' => true,
            'allow_bold' => true,
            'allow_italic' => true,
            'allow_blockquote' => true,
            'allow_image' => false,
            'allow_list' => true,
            'allow_nbsp' => true,
            'allow_nb_hyphen' => true,
            'allow_return' => true,
            'allow_link' => true,
            'allow_hr' => true,
            'allow_preview' => true,
        ]);

        $resolver->setAllowedTypes('allow_h2', ['boolean']);
        $resolver->setAllowedTypes('allow_h3', ['boolean']);
        $resolver->setAllowedTypes('allow_h4', ['boolean']);
        $resolver->setAllowedTypes('allow_h5', ['boolean']);
        $resolver->setAllowedTypes('allow_h6', ['boolean']);
        $resolver->setAllowedTypes('allow_bold', ['boolean']);
        $resolver->setAllowedTypes('allow_italic', ['boolean']);
        $resolver->setAllowedTypes('allow_blockquote', ['boolean']);
        $resolver->setAllowedTypes('allow_image', ['boolean']);
        $resolver->setAllowedTypes('allow_list', ['boolean']);
        $resolver->setAllowedTypes('allow_nbsp', ['boolean']);
        $resolver->setAllowedTypes('allow_nb_hyphen', ['boolean']);
        $resolver->setAllowedTypes('allow_return', ['boolean']);
        $resolver->setAllowedTypes('allow_link', ['boolean']);
        $resolver->setAllowedTypes('allow_hr', ['boolean']);
        $resolver->setAllowedTypes('allow_preview', ['boolean']);
    }
}
