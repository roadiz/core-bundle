<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\EventSubscriber;

use RZ\Roadiz\Core\Events\DocumentCreatedEvent;
use RZ\Roadiz\Core\Events\DocumentUpdatedEvent;
use RZ\Roadiz\Core\Events\FilterDocumentEvent;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentAudioVideoMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentAverageColorMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentExifMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentFilesizeMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentRawMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentSizeMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentSvgMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class DocumentMessageDispatchSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $bus;

    /**
     * @param MessageBusInterface $bus
     */
    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
             DocumentCreatedEvent::class => ['onFilterDocumentEvent', 0],
             DocumentUpdatedEvent::class => ['onFilterDocumentEvent', 0],
        ];
    }

    public function onFilterDocumentEvent(FilterDocumentEvent $event)
    {
        $document = $event->getDocument();
        if (
            $document instanceof Document &&
            null !== $document->getId() &&
            $document->isLocal() &&
            null !== $document->getRelativePath()
        ) {
            $this->bus->dispatch(new Envelope(new DocumentRawMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentFilesizeMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentSizeMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentAverageColorMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentExifMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentSvgMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentAudioVideoMessage($document->getId())));
        }
    }
}
