<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

final class DocumentFilesizeMessageHandler extends AbstractLockingDocumentMessageHandler
{
    /**
     * @param  DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && null !== $document->getRelativePath();
    }

    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        if (!$document instanceof AdvancedDocumentInterface) {
            return;
        }
        $documentPath = $this->packages->getDocumentFilePath($document);
        try {
            $file = new File($documentPath);
            $document->setFilesize($file->getSize());
        } catch (FileNotFoundException $exception) {
            $this->logger->warning(
                'Document file not found.',
                [
                    'path' => $documentPath,
                ]
            );
        }
    }
}
