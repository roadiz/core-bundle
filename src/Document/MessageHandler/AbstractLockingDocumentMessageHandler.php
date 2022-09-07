<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;

/**
 * Use file locking system to ensure only one async operation is done on each document file.
 */
abstract class AbstractLockingDocumentMessageHandler extends AbstractDocumentMessageHandler
{
    public function __invoke(AbstractDocumentMessage $message): void
    {
        $document = $this->managerRegistry->getRepository(Document::class)->find($message->getDocumentId());

        if ($document instanceof Document && $this->supports($document)) {
            $documentPath = $this->packages->getDocumentFilePath($document);
            $resource = \fopen($documentPath, "r+");

            if (false === $resource) {
                throw new RecoverableMessageHandlingException(sprintf(
                    '%s file does not exist',
                    $documentPath
                ));
            }

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
