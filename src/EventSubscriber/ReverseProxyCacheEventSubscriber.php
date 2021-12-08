<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Cache\ReverseProxyCacheLocator;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Message\GuzzleRequestMessage;
use RZ\Roadiz\CoreBundle\Message\PurgeReverseProxyCacheMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\Event;

final class ReverseProxyCacheEventSubscriber implements EventSubscriberInterface
{
    private ReverseProxyCacheLocator $reverseProxyCacheLocator;
    private LoggerInterface $logger;
    private MessageBusInterface $bus;

    /**
     * @param ReverseProxyCacheLocator $reverseProxyCacheLocator
     * @param MessageBusInterface $bus
     * @param LoggerInterface $logger
     */
    public function __construct(
        ReverseProxyCacheLocator $reverseProxyCacheLocator,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->bus = $bus;
        $this->reverseProxyCacheLocator = $reverseProxyCacheLocator;
    }
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CachePurgeRequestEvent::class => ['onBanRequest', 3],
            \RZ\Roadiz\Core\Events\Cache\CachePurgeRequestEvent::class => ['onBanRequest', 3],
            NodesSourcesUpdatedEvent::class => ['onPurgeRequest', 3],
            \RZ\Roadiz\Core\Events\NodesSources\NodesSourcesUpdatedEvent::class => ['onPurgeRequest', 3],
            'workflow.node.completed' => ['onNodeWorkflowCompleted', 3],
        ];
    }

    /**
     * @return bool
     */
    protected function supportConfig(): bool
    {
        return count($this->reverseProxyCacheLocator->getFrontends()) > 0;
    }

    /**
     * @param Event $event
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function onNodeWorkflowCompleted(Event $event): void
    {
        $node = $event->getSubject();
        if ($node instanceof Node) {
            if (!$this->supportConfig()) {
                return;
            }
            foreach ($node->getNodeSources() as $nodeSource) {
                $this->purgeNodesSources($nodeSource);
            }
        }
    }

    /**
     * @param CachePurgeRequestEvent $event
     */
    public function onBanRequest(CachePurgeRequestEvent $event)
    {
        if (!$this->supportConfig()) {
            return;
        }

        foreach ($this->createBanRequests() as $name => $request) {
            $this->sendRequest($request);
            $event->addMessage(
                'Reverse proxy cache cleared.',
                static::class,
                'Reverse proxy cache [' . $name . ']'
            );
        }
    }

    /**
     * @param NodesSourcesUpdatedEvent $event
     */
    public function onPurgeRequest(NodesSourcesUpdatedEvent $event)
    {
        if (!$this->supportConfig()) {
            return;
        }

        $this->purgeNodesSources($event->getNodeSource());
    }

    /**
     * @return \GuzzleHttp\Psr7\Request[]
     */
    protected function createBanRequests()
    {
        $requests = [];
        foreach ($this->reverseProxyCacheLocator->getFrontends() as $frontend) {
            $requests[$frontend->getName()] = new \GuzzleHttp\Psr7\Request(
                'BAN',
                'http://' . $frontend->getHost(),
                [
                    'Host' => $frontend->getDomainName()
                ]
            );
        }
        return $requests;
    }

    /**
     * @param NodesSources $nodeSource
     */
    protected function purgeNodesSources(NodesSources $nodeSource): void
    {
        try {
            $this->bus->dispatch(new Envelope(new PurgeReverseProxyCacheMessage($nodeSource->getId())));
        } catch (ExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @param \GuzzleHttp\Psr7\Request $request
     * @return void
     */
    protected function sendRequest(\GuzzleHttp\Psr7\Request $request): void
    {
        try {
            $this->bus->dispatch(new Envelope(new GuzzleRequestMessage($request, [
                'debug' => false,
                'timeout' => 3
            ])));
        } catch (ExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
