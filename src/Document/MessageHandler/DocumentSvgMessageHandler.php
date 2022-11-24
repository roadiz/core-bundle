<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use enshrined\svgSanitize\Sanitizer;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;
use RZ\Roadiz\Documents\SvgSizeResolver;

final class DocumentSvgMessageHandler extends AbstractLockingDocumentMessageHandler
{
    /**
     * @param  DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && null !== $document->getRelativePath() && $document->isSvg();
    }

    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        if (!$document instanceof SizeableInterface) {
            return;
        }
        $documentPath = $this->packages->getDocumentFilePath($document);

        // Create a new sanitizer instance
        $sanitizer = new Sanitizer();
        $sanitizer->minify(true);

        // Load the dirty svg
        $dirtySVG = file_get_contents($documentPath);
        if (false !== $dirtySVG) {
            file_put_contents($documentPath, $sanitizer->sanitize($dirtySVG));
            $this->logger->info('Svg document sanitized.');
        }

        /*
         * Resolve SVG size
         */
        try {
            $svgSizeResolver = new SvgSizeResolver($document, $this->packages);
            $document->setImageWidth($svgSizeResolver->getWidth());
            $document->setImageHeight($svgSizeResolver->getHeight());
        } catch (\RuntimeException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
