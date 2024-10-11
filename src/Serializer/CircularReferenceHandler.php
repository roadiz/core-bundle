<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\Operation;

final class CircularReferenceHandler
{
    private IriConverterInterface $iriConverter;

    /**
     * @param IriConverterInterface $iriConverter
     */
    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    public function __invoke(mixed $object, string $format, array $context): ?string
    {
        try {
            return $this->iriConverter->getIriFromResource(
                $object,
                UrlGeneratorInterface::ABS_PATH,
                null,
                $context
            );
        } catch (\InvalidArgumentException $exception) {
            if (is_object($object) && method_exists($object, 'getId')) {
                return (string) $object->getId();
            }
            return '';
        }
    }
}
