<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

final class RoleEntityType extends AbstractType
{
    private ManagerRegistry $managerRegistry;
    private Security $security;

    public function __construct(ManagerRegistry $managerRegistry, Security $security)
    {
        $this->managerRegistry = $managerRegistry;
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => false,
            'choice_label' => function (?Role $choice) {
                return $choice ? $choice->getRole() : '';
            },
            'choice_value' => function (?Role $choice) {
                return $choice ? $choice->getId() : '';
            },
        ]);

        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $roles = $this->managerRegistry->getRepository(Role::class)->findAll();

            /** @var Role $role */
            foreach ($roles as $role) {
                if ($this->security->isGranted($role->getRole())) {
                    $choices[] = $role;
                }
            }
            return $choices;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'role_entity';
    }
}
