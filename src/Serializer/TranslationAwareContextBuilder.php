<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Component\HttpFoundation\Request;

final class TranslationAwareContextBuilder implements SerializerContextBuilderInterface
{
    private ManagerRegistry $managerRegistry;
    private SerializerContextBuilderInterface $decorated;

    public function __construct(
        SerializerContextBuilderInterface $decorated,
        ManagerRegistry $managerRegistry
    ) {
        $this->decorated = $decorated;
        $this->managerRegistry = $managerRegistry;
    }
    /**
     * @inheritDoc
     */
    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (!isset($context['translation']) || !($context['translation'] instanceof TranslationInterface)) {
            if (!empty($request->query->get('_locale'))) {
                /** @var TranslationRepository $repository */
                $repository = $this->managerRegistry
                    ->getRepository(TranslationInterface::class);
                $translation = $repository->findOneAvailableByLocaleOrOverrideLocale($request->query->get('_locale'));

                if ($translation instanceof TranslationInterface) {
                    $context['translation'] = $translation;
                }
            }
        }
        return $context;
    }
}
