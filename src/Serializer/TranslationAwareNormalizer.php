<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class TranslationAwareNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private RequestStack $requestStack;
    private ManagerRegistry $managerRegistry;

    private const ALREADY_CALLED = 'TRANSLATION_AWARE_NORMALIZER_ALREADY_CALLED';

    /**
     * @param RequestStack $requestStack
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(RequestStack $requestStack, ManagerRegistry $managerRegistry)
    {
        $this->requestStack = $requestStack;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param $object
     * @param $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!isset($context['translation']) || !($context['translation'] instanceof TranslationInterface)) {
            if ($object instanceof NodesSources) {
                $context['translation'] = $object->getTranslation();
            } else {
                $translation = $this->getTranslationFromRequest();
                if (null !== $translation) {
                    $context['translation'] = $translation;
                }
            }
        }

        $context[self::ALREADY_CALLED] = true;

        return $this->normalizer->normalize($object, $format, $context);
    }

    private function getTranslationFromRequest(): ?TranslationInterface
    {
        $request = $this->requestStack->getMainRequest();
        if (null !== $request && !empty($request->query->get('_locale'))) {
            return $this->managerRegistry
                ->getRepository(Translation::class)
                ->findOneAvailableByLocaleOrOverrideLocale($request->query->get('_locale'));
        }

        return $this->managerRegistry
            ->getRepository(Translation::class)
            ->findDefault();
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return true;
    }
}
