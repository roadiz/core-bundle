<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Cache\Clearer\NodesSourcesUrlsCacheClearer;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NodesSourcesUrlsCacheEventSubscriber implements EventSubscriberInterface
{
    private NodesSourcesUrlsCacheClearer $cacheClearer;

    /**
     * @param NodesSourcesUrlsCacheClearer $cacheClearer
     */
    public function __construct(NodesSourcesUrlsCacheClearer $cacheClearer)
    {
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CachePurgeRequestEvent::class => ['onPurgeRequest', 3],
            \RZ\Roadiz\Core\Events\Cache\CachePurgeRequestEvent::class => ['onPurgeRequest', 3],
        ];
    }

    /**
     * @param CachePurgeRequestEvent $event
     */
    public function onPurgeRequest(CachePurgeRequestEvent $event)
    {
        try {
            if (false !== $this->cacheClearer->clear()) {
                $event->addMessage($this->cacheClearer->getOutput(), static::class, 'NodesSources URL cache');
            }
        } catch (\Exception $e) {
            $event->addError($e->getMessage(), static::class, 'NodesSources URL cache');
        }
    }
}
