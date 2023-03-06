<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @package RZ\Roadiz\CoreBundle\Form\DataTransformer
 */
class EntityCollectionTransformer implements DataTransformerInterface
{
    protected bool $asCollection;
    private ObjectManager $manager;
    /**
     * @var class-string<PersistableInterface>
     */
    private string $classname;

    /**
     * @param ObjectManager $manager
     * @param class-string<PersistableInterface> $classname
     * @param bool $asCollection
     */
    public function __construct(ObjectManager $manager, string $classname, bool $asCollection = false)
    {
        $this->manager = $manager;
        $this->asCollection = $asCollection;
        $this->classname = $classname;
    }

    /**
     * @param ArrayCollection<PersistableInterface>|PersistableInterface[]|null $value
     * @return string|array
     */
    public function transform(mixed $value): string|array
    {
        if (empty($value)) {
            return '';
        }
        $ids = [];
        /** @var PersistableInterface $entity */
        foreach ($value as $entity) {
            $ids[] = $entity->getId();
        }
        if ($this->asCollection) {
            return $ids;
        }
        return implode(',', $ids);
    }

    /**
     * @param string|array|null $value
     * @return array<PersistableInterface>|ArrayCollection<PersistableInterface>
     */
    public function reverseTransform(mixed $value): array|ArrayCollection
    {
        if (!$value) {
            if ($this->asCollection) {
                return new ArrayCollection();
            }
            return [];
        }

        if (is_array($value)) {
            $ids = $value;
        } else {
            $ids = explode(',', $value);
        }

        /** @var array<PersistableInterface> $entities */
        $entities = [];
        foreach ($ids as $entityId) {
            /** @var PersistableInterface|null $entity */
            $entity = $this->manager
                ->getRepository($this->classname)
                ->find($entityId)
            ;
            if (null === $entity) {
                throw new TransformationFailedException(sprintf(
                    'A %s with id "%s" does not exist!',
                    $this->classname,
                    $entityId
                ));
            }

            $entities[] = $entity;
        }
        if ($this->asCollection) {
            return new ArrayCollection($entities);
        }
        return $entities;
    }
}
