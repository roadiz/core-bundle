<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook\Message;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use RZ\Roadiz\CoreBundle\Message\AsyncMessage;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessage;
use RZ\Roadiz\CoreBundle\Entity\Webhook;
use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;

final class GenericJsonPostMessage implements AsyncMessage, HttpRequestMessage, WebhookMessage
{
    public function __construct(
        private readonly string $uri,
        private readonly ?array $payload = null
    ) {
    }

    public function getRequest(): RequestInterface
    {
        return new Request(
            'POST',
            $this->uri,
            [
                'Content-Type' => 'application/json',
                'Accept'     => 'application/json'
            ],
            json_encode($this->payload ?? [], JSON_NUMERIC_CHECK | JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return [
            'debug' => false,
            'timeout' => 3
        ];
    }

    /**
     * @param Webhook $webhook
     * @return static
     */
    public static function fromWebhook(WebhookInterface $webhook): self
    {
        return new self($webhook->getUri(), $webhook->getPayload());
    }
}
