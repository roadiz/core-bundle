<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Cache\Clearer\AssetsFileClearer;
use RZ\Roadiz\Documents\Events\CachePurgeAssetsRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AssetsCacheEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetsFileClearer $assetsClearer,
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CachePurgeAssetsRequestEvent::class => ['onPurgeAssetsRequest', 0]
        ];
    }

    public function onPurgeAssetsRequest(CachePurgeAssetsRequestEvent $event): void
    {
        try {
            $this->assetsClearer->clear();
            $this->logger->info($this->assetsClearer->getOutput());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
