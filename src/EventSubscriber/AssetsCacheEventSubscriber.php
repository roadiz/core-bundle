<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Cache\Clearer\AssetsFileClearer;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeAssetsRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AssetsCacheEventSubscriber implements EventSubscriberInterface
{
    private AssetsFileClearer $assetsClearer;

    /**
     * @param AssetsFileClearer $assetsClearer
     */
    public function __construct(AssetsFileClearer $assetsClearer)
    {
        $this->assetsClearer = $assetsClearer;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CachePurgeAssetsRequestEvent::class => ['onPurgeAssetsRequest', 0],
            \RZ\Roadiz\Core\Events\Cache\CachePurgeAssetsRequestEvent::class => ['onPurgeAssetsRequest', 0],
        ];
    }

    /**
     * @param CachePurgeAssetsRequestEvent $event
     */
    public function onPurgeAssetsRequest(CachePurgeAssetsRequestEvent $event)
    {
        try {
            $this->assetsClearer->clear();
            $event->addMessage($this->assetsClearer->getOutput(), static::class, 'Assets cache');
        } catch (\Exception $e) {
            $event->addError($e->getMessage(), static::class, 'Assets cache');
        }
    }
}
