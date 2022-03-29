<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class NodesSourcesPathNormalizer implements ContextAwareNormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private UrlGeneratorInterface $urlGenerator;
    /**
     * @var DenormalizerInterface&NormalizerInterface
     */
    private $decorated;

    public function __construct(NormalizerInterface $decorated, UrlGeneratorInterface $urlGenerator)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if ($object instanceof NodesSources && $object->isReachable() && is_array($data) && !isset($data['url'])) {
            $data['url'] = $this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $object
                ],
                UrlGeneratorInterface::ABSOLUTE_PATH
            );
        }
        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param string|null $format
     * @param array $context
     * @return mixed
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}
