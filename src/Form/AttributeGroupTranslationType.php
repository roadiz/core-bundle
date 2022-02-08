<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Form\Constraint\UniqueEntity;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\TranslationTransformer;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroupTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class AttributeGroupTranslationType extends AbstractType
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
                'empty_data' => '',
                'label' => false,
                'required' => false,
            ])
            ->add('translation', TranslationsType::class, [
                'label' => false,
                'required' => true,
                'constraints' => [
                    new NotNull()
                ]
            ])
        ;

        $builder->get('translation')->addModelTransformer(new TranslationTransformer($this->managerRegistry));
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', AttributeGroupTranslation::class);
        $resolver->setDefault('constraints', [
            new UniqueEntity([
                'fields' => ['name', 'translation'],
            ])
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix(): string
    {
        return 'attribute_group_translation';
    }
}
