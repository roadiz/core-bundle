<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor\TypedObjectConstructorInterface;

final readonly class RolesImporter implements EntityImporterInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function supports(string $entityClass): bool
    {
        return Role::class === $entityClass;
    }

    public function import(string $serializedData): bool
    {
        $this->serializer->deserialize(
            $serializedData,
            'array<'.Role::class.'>',
            'json',
            DeserializationContext::create()
                ->setAttribute(TypedObjectConstructorInterface::PERSIST_NEW_OBJECTS, true)
                ->setAttribute(TypedObjectConstructorInterface::FLUSH_NEW_OBJECTS, true)
        );

        return true;
    }
}
