<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Node types selector form field type.
 */
class NodeTypesType extends AbstractType
{
    protected ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'showInvisible' => false,
        ]);
        $resolver->setAllowedTypes('showInvisible', ['boolean']);
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $criteria = [];
            if (false === $options['showInvisible']) {
                $criteria['visible'] = true;
            }
            $nodeTypes = $this->managerRegistry->getRepository(NodeType::class)->findBy($criteria);

            /** @var NodeType $nodeType */
            foreach ($nodeTypes as $nodeType) {
                $choices[$nodeType->getDisplayName()] = $nodeType->getId();
            }
            ksort($choices);

            return $choices;
        });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'node_types';
    }
}
