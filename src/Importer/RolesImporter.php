<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor\TypedObjectConstructorInterface;

class RolesImporter implements EntityImporterInterface
{
    protected SerializerInterface $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $entityClass): bool
    {
        return $entityClass === Role::class;
    }

    /**
     * @inheritDoc
     */
    public function import(string $serializedData): bool
    {
        $this->serializer->deserialize(
            $serializedData,
            'array<' . Role::class . '>',
            'json',
            DeserializationContext::create()
                ->setAttribute(TypedObjectConstructorInterface::PERSIST_NEW_OBJECTS, true)
                ->setAttribute(TypedObjectConstructorInterface::FLUSH_NEW_OBJECTS, true)
        );

        return true;
    }
}
