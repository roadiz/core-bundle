<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Override Document default normalization.
 */
final class DocumentNormalizer extends AbstractPathNormalizer
{
    private FilesystemOperator $documentsStorage;

    public function __construct(
        FilesystemOperator $documentsStorage,
        NormalizerInterface $decorated,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($decorated, $urlGenerator);
        $this->documentsStorage = $documentsStorage;
    }

    /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if (
            $object instanceof Document &&
            is_array($data)
        ) {
            $data['type'] = $object->getShortType();

            if (
                !$object->isPrivate() &&
                !$object->isProcessable()
            ) {
                $mountPath = $object->getMountPath();
                if (null !== $mountPath) {
                    $data['publicUrl'] = $this->documentsStorage->publicUrl($mountPath);
                }
            }

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
        }
        return $data;
    }
}
