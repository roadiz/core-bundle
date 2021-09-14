<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Cache\CloudflareProxyCache;
use RZ\Roadiz\CoreBundle\Cache\ReverseProxyCacheLocator;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Message\GuzzleRequestMessage;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CloudflareCacheEventSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private MessageBusInterface $bus;
    private UrlGeneratorInterface $urlGenerator;
    private ReverseProxyCacheLocator $reverseProxyCacheLocator;

    /**
     * @param MessageBusInterface $bus
     * @param ReverseProxyCacheLocator $reverseProxyCacheLocator
     * @param UrlGeneratorInterface $urlGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        MessageBusInterface $bus,
        ReverseProxyCacheLocator $reverseProxyCacheLocator,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->bus = $bus;
        $this->reverseProxyCacheLocator = $reverseProxyCacheLocator;
        $this->urlGenerator = $urlGenerator;
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
        ];
    }

    /**
     * @return bool
     */
    protected function supportConfig(): bool
    {
        return null !== $this->reverseProxyCacheLocator->getCloudflareProxyCache();
    }

    /**
     * @param CachePurgeRequestEvent $event
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return void
     */
    public function onBanRequest(CachePurgeRequestEvent $event)
    {
        if (!$this->supportConfig()) {
            return;
        }
        try {
            $request = $this->createBanRequest();
            $this->sendRequest($request);
            $event->addMessage(
                'Cloudflare cache cleared.',
                static::class,
                'Cloudflare proxy cache'
            );
        } catch (RequestException $e) {
            if (null !== $e->getResponse()) {
                $data = \json_decode($e->getResponse()->getBody()->getContents(), true);
                $event->addError(
                    $data['errors'][0]['message'] ?? $e->getMessage(),
                    static::class,
                    'Cloudflare proxy cache'
                );
            } else {
                $event->addError(
                    $e->getMessage(),
                    static::class,
                    'Cloudflare proxy cache'
                );
            }
        } catch (ConnectException $e) {
            $event->addError(
                $e->getMessage(),
                static::class,
                'Cloudflare proxy cache'
            );
        }
    }

    /**
     * @param NodesSourcesUpdatedEvent $event
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function onPurgeRequest(NodesSourcesUpdatedEvent $event)
    {
        if (!$this->supportConfig()) {
            return;
        }

        try {
            $nodeSource = $event->getNodeSource();
            while (!$nodeSource->isReachable()) {
                $nodeSource = $nodeSource->getParent();
                if (null === $nodeSource) {
                    return;
                }
            }

            $purgeRequest = $this->createPurgeRequest([$this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $nodeSource,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )]);
            $this->sendRequest($purgeRequest);
        } catch (ClientException $e) {
            // do nothing
        }
    }

    private function getCloudflareCacheProxy(): CloudflareProxyCache
    {
        $proxy = $this->reverseProxyCacheLocator->getCloudflareProxyCache();
        if (null === $proxy) {
            throw new \RuntimeException('Cloudflare cache proxy is not configured');
        }
        return $proxy;
    }

    /**
     * @param array $body
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function createRequest(array $body): \GuzzleHttp\Psr7\Request
    {
        $headers = [
            'Content-type' => 'application/json',
        ];
        $headers['Authorization'] = 'Bearer ' . trim($this->getCloudflareCacheProxy()->getBearer());
        $headers['X-Auth-Email'] = $this->getCloudflareCacheProxy()->getEmail();
        $headers['X-Auth-Key'] = $this->getCloudflareCacheProxy()->getKey();

        $uri = sprintf(
            'https://api.cloudflare.com/client/%s/zones/%s/purge_cache',
            $this->getCloudflareCacheProxy()->getVersion(),
            $this->getCloudflareCacheProxy()->getZone()
        );
        return new \GuzzleHttp\Psr7\Request(
            'POST',
            $uri,
            $headers,
            \json_encode($body)
        );
    }

    /**
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function createBanRequest()
    {
        return $this->createRequest([
            'purge_everything' => true,
        ]);
    }

    /**
     * @param string[] $uris
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function createPurgeRequest(array $uris = [])
    {
        return $this->createRequest([
            'files' => $uris
        ]);
    }

    /**
     * @param RequestInterface $request
     * @return void
     */
    protected function sendRequest(RequestInterface $request): void
    {
        try {
            $this->bus->dispatch(new Envelope(new GuzzleRequestMessage($request, [
                'debug' => false,
                'timeout' => $this->getCloudflareCacheProxy()->getTimeout()
            ])));
        } catch (ExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
