<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\Cache\ReverseProxyCacheLocator;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Message\GuzzleRequestMessage;
use RZ\Roadiz\CoreBundle\Message\PurgeReverseProxyCacheMessage;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PurgeReverseProxyCacheMessageHandler implements MessageHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private ReverseProxyCacheLocator $reverseProxyCacheLocator;
    private LoggerInterface $logger;
    private MessageBusInterface $bus;
    private ManagerRegistry $managerRegistry;

    /**
     * @param MessageBusInterface $bus
     * @param UrlGeneratorInterface $urlGenerator
     * @param ReverseProxyCacheLocator $reverseProxyCacheLocator
     * @param ManagerRegistry $managerRegistry
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        MessageBusInterface $bus,
        UrlGeneratorInterface $urlGenerator,
        ReverseProxyCacheLocator $reverseProxyCacheLocator,
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger = null
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->reverseProxyCacheLocator = $reverseProxyCacheLocator;
        $this->logger = $logger ?? new NullLogger();
        $this->managerRegistry = $managerRegistry;
        $this->bus = $bus;
    }

    public function __invoke(PurgeReverseProxyCacheMessage $message): void
    {
        $nodeSource = $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->find($message->getNodeSourceId());
        if (null === $nodeSource) {
            throw new UnrecoverableMessageHandlingException('NodesSources does not exist anymore.');
        }

        while (!$nodeSource->isReachable()) {
            $nodeSource = $nodeSource->getParent();
            if (null === $nodeSource) {
                return;
            }
        }

        $purgeRequests = $this->createPurgeRequests($this->urlGenerator->generate(
            RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
            [
                RouteObjectInterface::ROUTE_OBJECT => $nodeSource,
            ]
        ));
        foreach ($purgeRequests as $request) {
            $this->sendRequest($request);
        }
    }

    /**
     * @param string $path
     *
     * @return \GuzzleHttp\Psr7\Request[]
     */
    protected function createPurgeRequests(string $path = "/"): array
    {
        $requests = [];
        foreach ($this->reverseProxyCacheLocator->getFrontends() as $frontend) {
            $requests[$frontend->getName()] = new \GuzzleHttp\Psr7\Request(
                Request::METHOD_PURGE,
                'http://' . $frontend->getHost() . $path,
                [
                    'Host' => $frontend->getDomainName()
                ]
            );
        }
        return $requests;
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
        } catch (NoHandlerForMessageException $exception) {
            throw new UnrecoverableMessageHandlingException($exception->getMessage(), 0, $exception);
        }
    }
}
