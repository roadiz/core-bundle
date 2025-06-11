<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodePathChangedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeMover;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to Node, NodesSources and UrlAlias event to clear ns url cache.
 */
final readonly class NodeRedirectionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NodeMover $nodeMover,
        private string $kernelEnvironment,
        private PreviewResolverInterface $previewResolver,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NodePathChangedEvent::class => 'redirectOldPaths',
        ];
    }

    /**
     * Empty nodeSources Url cache.
     */
    public function redirectOldPaths(
        NodePathChangedEvent $event,
        string $eventName,
        EventDispatcherInterface $dispatcher,
    ): void {
        if (
            'prod' === $this->kernelEnvironment
            && !$this->previewResolver->isPreview()
            && null !== $event->getNode()
            && $event->getNode()->isPublished()
            && $event->getNode()->getNodeType()->isReachable()
            && count($event->getPaths()) > 0
        ) {
            $this->nodeMover->redirectAll($event->getNode(), $event->getPaths());
            $dispatcher->dispatch(new CachePurgeRequestEvent());
        }
    }
}
