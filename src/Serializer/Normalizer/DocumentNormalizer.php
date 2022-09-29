<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\Document\DocumentFinderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Override Document default normalization.
 */
final class DocumentNormalizer extends AbstractPathNormalizer
{
    protected DocumentFinderInterface $documentFinder;

    public function __construct(
        NormalizerInterface $decorated,
        UrlGeneratorInterface $urlGenerator,
        DocumentFinderInterface $documentFinder
    ) {
        parent::__construct($decorated, $urlGenerator);
        $this->documentFinder = $documentFinder;
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
        if ($object instanceof Document && is_array($data)) {
            $data['type'] = $object->getShortType();

            /** @var array<string> $serializationGroups */
            $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];

            if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
                $translatedData = $object->getDocumentTranslationsByTranslation($context['translation'])->first() ?: null;
                if ($translatedData instanceof DocumentTranslation) {
                    $data['name'] = $translatedData->getName();
                    $data['description'] = $translatedData->getDescription();
                    $data['copyright'] = $translatedData->getCopyright();
                    $data['alt'] = !empty($translatedData->getName()) ? $translatedData->getName() : $object->getFilename();
                    $data['externalUrl'] = $translatedData->getExternalUrl();
                }
            }

            if (in_array('document_display_sources', $serializationGroups)) {
                if ($object->isLocal() && $object->isVideo()) {
                    $data['altSources'] = [];
                    foreach ($this->documentFinder->findVideosWithFilename($object->getRelativePath()) as $document) {
                        if ($document->getRelativePath() !== $object->getRelativePath()) {
                            $data['altSources'][] = $document;
                        }
                    }
                } elseif ($object->isLocal() && $object->isAudio()) {
                    $data['altSources'] = [];
                    foreach ($this->documentFinder->findAudiosWithFilename($object->getRelativePath()) as $document) {
                        if ($document->getRelativePath() !== $object->getRelativePath()) {
                            $data['altSources'][] = $document;
                        }
                    }
                }
            }
        }
        return $data;
    }
}
