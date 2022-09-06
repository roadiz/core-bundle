<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Utils\Asset\Packages;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

abstract class AbstractDocumentMessageHandler implements MessageHandlerInterface
{
    protected ManagerRegistry $managerRegistry;
    protected LoggerInterface $logger;
    protected Packages $packages;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param LoggerInterface $messengerLogger
     * @param Packages $packages
     */
    public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $messengerLogger, Packages $packages)
    {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $messengerLogger;
        $this->packages = $packages;
    }

    abstract protected function supports(DocumentInterface $document): bool;

    abstract protected function processMessage(AbstractDocumentMessage $message, Document $document): void;

    public function __invoke(AbstractDocumentMessage $message): void
    {
        $document = $this->managerRegistry->getRepository(Document::class)->find($message->getDocumentId());

        if ($document instanceof Document && $this->supports($document)) {
            $this->processMessage($message, $document);
            $this->managerRegistry->getManager()->flush();
        }
    }
}
