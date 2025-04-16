<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Realm\RealmResolver;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\RealmVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class RealmSerializationGroupNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'REALM_SERIALIZER_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly Security $security,
        private readonly RealmResolver $realmResolver,
        private readonly Stopwatch $stopwatch,
    ) {
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (!($data instanceof NodesSources)) {
            return false;
        }
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $this->realmResolver->hasRealmsWithSerializationGroup();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }

    /**
     * @return array|string|int|float|bool|\ArrayObject|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $this->stopwatch->start('realm-serialization-group-normalizer', 'serializer');
        $realms = $this->getAuthorizedRealmsForObject($object);

        foreach ($realms as $realm) {
            if (!empty($realm->getSerializationGroup())) {
                $context['groups'][] = $realm->getSerializationGroup();
            }
        }

        $context[self::ALREADY_CALLED] = true;
        $this->stopwatch->stop('realm-serialization-group-normalizer');

        return $this->normalizer->normalize($object, $format, $context);
    }

    /**
     * @return RealmInterface[]
     */
    private function getAuthorizedRealmsForObject(NodesSources $object): array
    {
        $realms = $this->realmResolver->getRealmsWithSerializationGroup($object->getNode());

        return array_filter($realms, function (RealmInterface $realm) {
            return $this->security->isGranted(RealmVoter::READ, $realm);
        });
    }
}
