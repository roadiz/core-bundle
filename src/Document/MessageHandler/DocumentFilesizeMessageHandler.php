<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
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

    protected function processMessage(AbstractDocumentMessage $message, Document $document): void
    {
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
