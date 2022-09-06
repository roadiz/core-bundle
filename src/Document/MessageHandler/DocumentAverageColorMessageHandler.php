<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Utils\Document\AverageColorResolver;

final class DocumentAverageColorMessageHandler extends AbstractLockingDocumentMessageHandler
{
    /**
     * @param  DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && $document->isProcessable();
    }

    protected function processMessage(AbstractDocumentMessage $message, Document $document): void
    {
        $documentPath = $this->packages->getDocumentFilePath($document);
        try {
            $manager = new ImageManager();
            $mediumColor = (new AverageColorResolver())->getAverageColor($manager->make($documentPath));
            $document->setImageAverageColor($mediumColor);
        } catch (NotReadableException $exception) {
            $this->logger->warning(
                'Document file is not a readable image.',
                [
                    'path' => $documentPath,
                    'message' => $exception->getMessage()
                ]
            );
        }
    }
}
