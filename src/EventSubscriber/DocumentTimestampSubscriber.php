<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\CoreBundle\Event\DocumentTranslationUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DocumentTimestampSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            DocumentTranslationUpdatedEvent::class => 'onDocumentTranslationUpdatedEvent'
        ];
    }

    public function onDocumentTranslationUpdatedEvent(DocumentTranslationUpdatedEvent $event)
    {
        $document = $event->getDocument();
        if ($document instanceof AbstractDateTimed) {
            $document->setUpdatedAt(new \DateTime());
        }
    }
}
