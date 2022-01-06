<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\Form\DataTransformerInterface;

class JoinDataTransformer implements DataTransformerInterface
{
    /**
     * @var NodeTypeField
     */
    private NodeTypeField $nodeTypeField;
    private ManagerRegistry $managerRegistry;
    /**
     * @var class-string
     */
    private string $entityClassname;

    /**
     * @param NodeTypeField $nodeTypeField
     * @param ManagerRegistry $managerRegistry
     * @param string $entityClassname
     */
    public function __construct(
        NodeTypeField $nodeTypeField,
        ManagerRegistry $managerRegistry,
        string $entityClassname
    ) {
        $this->nodeTypeField = $nodeTypeField;
        $this->entityClassname = $entityClassname;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param mixed $entitiesToForm
     * @return mixed
     */
    public function transform($entitiesToForm)
    {
        /*
         * If model is already an PersistableInterface
         */
        if (
            !empty($entitiesToForm) &&
            $entitiesToForm instanceof PersistableInterface
        ) {
            return $entitiesToForm->getId();
        } elseif (!empty($entitiesToForm) && is_array($entitiesToForm)) {
            /*
             * If model is a collection of AbstractEntity
             */
            $idArray = [];
            foreach ($entitiesToForm as $entity) {
                if ($entity instanceof PersistableInterface) {
                    $idArray[] = $entity->getId();
                }
            }
            return $idArray;
        } elseif (!empty($entitiesToForm)) {
            return $entitiesToForm;
        }
        return '';
    }

    /**
     * @param mixed $formToEntities
     * @return mixed
     */
    public function reverseTransform($formToEntities)
    {
        if ($this->nodeTypeField->isManyToMany()) {
            $unorderedEntities = $this->managerRegistry->getRepository($this->entityClassname)->findBy([
                'id' => $formToEntities,
            ]);
            /*
             * Need to preserve order in POST data
             */
            usort($unorderedEntities, function (PersistableInterface $a, PersistableInterface $b) use ($formToEntities) {
                return array_search($a->getId(), $formToEntities) -
                    array_search($b->getId(), $formToEntities);
            });
            return $unorderedEntities;
        }
        if ($this->nodeTypeField->isManyToOne()) {
            return $this->managerRegistry->getRepository($this->entityClassname)->findOneBy([
                'id' => $formToEntities,
            ]);
        }
        return null;
    }
}
