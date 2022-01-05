<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class NodesSourcesPathNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface
{
    private ObjectNormalizer $normalizer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, ObjectNormalizer $normalizer)
    {
        $this->urlGenerator = $urlGenerator;
        $this->normalizer = $normalizer;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        if ($object->isReachable() && is_array($data) && !isset($data['url'])) {
            $data['url'] = $this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $object
                ],
                UrlGeneratorInterface::RELATIVE_PATH
            );
        }
        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof NodesSources;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return __CLASS__ === NodesSourcesPathNormalizer::class;
    }
}
