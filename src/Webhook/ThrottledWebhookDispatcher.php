<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook;

use RZ\Roadiz\CoreBundle\Webhook\Exception\TooManyWebhookTriggeredException;
use RZ\Roadiz\CoreBundle\Webhook\Message\WebhookMessageFactoryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class ThrottledWebhookDispatcher implements WebhookDispatcher
{
    private WebhookMessageFactoryInterface $messageFactory;
    private MessageBusInterface $messageBus;
    private RateLimiterFactory $throttledWebhooksLimiter;

    public function __construct(
        WebhookMessageFactoryInterface $messageFactory,
        MessageBusInterface $messageBus,
        RateLimiterFactory $throttledWebhooksLimiter
    ) {
        $this->messageFactory = $messageFactory;
        $this->messageBus = $messageBus;
        $this->throttledWebhooksLimiter = $throttledWebhooksLimiter;
    }

    /**
     * @param WebhookInterface $webhook
     * @throws \Exception
     */
    public function dispatch(WebhookInterface $webhook): void
    {
        $doNotTriggerBefore = $webhook->doNotTriggerBefore();
        if (
            null !== $doNotTriggerBefore &&
            $doNotTriggerBefore > new \DateTime()
        ) {
            throw new TooManyWebhookTriggeredException(\DateTimeImmutable::createFromMutable($doNotTriggerBefore));
        }
        $limiter = $this->throttledWebhooksLimiter->create($webhook->getId());
        $limit = $limiter->consume();
        // the argument of consume() is the number of tokens to consume
        // and returns an object of type Limit
        if (!$limit->isAccepted()) {
            throw new TooManyWebhookTriggeredException($limit->getRetryAfter());
        }
        $message = $this->messageFactory->createMessage($webhook);
        $this->messageBus->dispatch(new Envelope($message));
        $webhook->setLastTriggeredAt(new \DateTime());
    }
}
