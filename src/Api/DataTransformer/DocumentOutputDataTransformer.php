<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\DocumentOutput;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;

class DocumentOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = [])
    {
        if (!$data instanceof Document) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . DocumentInterface::class);
        }
        $output = new DocumentOutput();
        $output->relativePath = $data->getRelativePath();
        $output->processable = $data->isProcessable();
        $output->type = $data->getShortType();
        $output->alt = $data->getFilename();
        $output->embedId = $data->getEmbedId();
        $output->embedPlatform = $data->getEmbedPlatform();
        $output->imageAverageColor = $data->getImageAverageColor();
        $output->folders = $data->getFolders()->toArray();

        if (false !== $data->getThumbnails()->first()) {
            $output->thumbnail = $data->getThumbnails()->first();
        }

        if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
            $translatedData = $data->getDocumentTranslationsByTranslation($context['translation'])->first() ?: null;
            if ($translatedData instanceof DocumentTranslation) {
                $output->name = $translatedData->getName();
                $output->description = $translatedData->getDescription();
                $output->copyright = $translatedData->getCopyright();
                $output->alt = !empty($translatedData->getName()) ? $translatedData->getName() : $data->getFilename();
            }
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return DocumentOutput::class === $to && $data instanceof DocumentInterface;
    }
}
