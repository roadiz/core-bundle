<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transform Doctrine integer ID to their Doctrine entities.
 */
class ReversePersistableTransformer implements DataTransformerInterface
{
    /**
     * @var class-string<PersistableInterface>
     */
    protected string $doctrineEntity;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @param class-string<PersistableInterface> $doctrineEntity
     */
    public function __construct(EntityManagerInterface $entityManager, string $doctrineEntity)
    {
        $this->entityManager = $entityManager;
        $this->doctrineEntity = $doctrineEntity;
    }

    public function transform(mixed $value): ?array
    {
        if (null === $value) {
            return null;
        }
        return $this->entityManager->getRepository($this->doctrineEntity)->findBy([
            'id' => $value
        ]);
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(function (PersistableInterface $item) {
                return $item->getId();
            }, $value);
        }
        if ($value instanceof PersistableInterface) {
            return $value->getId();
        }
        return null;
    }
}
