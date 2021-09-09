<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Cache\Clearer\OPCacheClearer;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class OPCacheEventSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CachePurgeRequestEvent::class => ['onPurgeRequest', 3],
        ];
    }

    /**
     * @param CachePurgeRequestEvent $event
     */
    public function onPurgeRequest(CachePurgeRequestEvent $event)
    {
        try {
            $clearer = new OPCacheClearer();
            if (false !== $clearer->clear()) {
                $event->addMessage($clearer->getOutput(), static::class, 'OPCode cache');
            }
        } catch (\Exception $e) {
            $event->addError($e->getMessage(), static::class, 'OPCode cache');
        }
    }
}
