<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transform Doctrine entities to their unique identifier.
 */
final readonly class PersistableTransformer implements DataTransformerInterface
{
    /**
     * @param class-string<PersistableInterface> $doctrineEntity
     */
    public function __construct(private EntityManagerInterface $entityManager, private string $doctrineEntity)
    {
    }

    public function transform(mixed $value): mixed
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

    public function reverseTransform(mixed $value): ?array
    {
        if (null === $value) {
            return null;
        }

        return $this->entityManager->getRepository($this->doctrineEntity)->findBy([
            'id' => $value,
        ]);
    }
}
