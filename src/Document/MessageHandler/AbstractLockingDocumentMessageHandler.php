<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;

/**
 * Use file locking system to ensure only one async operation is done on each document file.
 */
abstract class AbstractLockingDocumentMessageHandler extends AbstractDocumentMessageHandler
{
    public function __invoke(AbstractDocumentMessage $message): void
    {
        $document = $this->managerRegistry
            ->getRepository(DocumentInterface::class)
            ->find($message->getDocumentId());

        if ($document instanceof DocumentInterface && $this->supports($document)) {
            $documentPath = $document->getMountPath();
            $resource = $this->documentsStorage->readStream($documentPath);

            if (\flock($resource, \LOCK_EX)) {
                $this->processMessage($message, $document);
                $this->managerRegistry->getManager()->flush();
                \flock($resource, \LOCK_UN);
            } else {
                throw new RecoverableMessageHandlingException(sprintf(
                    '%s file is currently locked',
                    $documentPath
                ));
            }
        }
    }
}
