<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Component\HttpFoundation\Request;

final readonly class TranslationAwareContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private SerializerContextBuilderInterface $decorated,
        private ManagerRegistry $managerRegistry,
        private PreviewResolverInterface $previewResolver,
    ) {
    }

    #[\Override]
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
            // @phpstan-ignore-next-line
            return $context;
        }

        /*
         * Try to get translation resolved from LocaleSubscriber before
         */
        $requestTranslation = $request->attributes->get('_translation');
        if ($requestTranslation instanceof TranslationInterface) {
            $context['translation'] = $requestTranslation;

            return $context;
        }

        /** @var TranslationRepository $repository */
        $repository = $this->managerRegistry
            ->getRepository(TranslationInterface::class);
        $locale = $request->query->get('_locale', $request->getLocale());

        if (!\is_string($locale)) {
            return $context;
        }

        if ($this->previewResolver->isPreview()) {
            $translation = $repository->findOneByLocaleOrOverrideLocale($locale);
        } else {
            $translation = $repository->findOneAvailableByLocaleOrOverrideLocale($locale);
        }

        if ($translation instanceof TranslationInterface) {
            $context['translation'] = $translation;
        }

        return $context;
    }
}
