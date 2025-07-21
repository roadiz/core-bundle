<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Captcha;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GoogleRecaptchaService implements CaptchaServiceInterface
{
    public function __construct(
        private HttpClientInterface $client,
        protected ?string $publicKey,
        #[\SensitiveParameter]
        protected ?string $privateKey,
        protected ?string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify',
    ) {
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return !empty($this->publicKey) && !empty($this->privateKey) && !empty($this->verifyUrl);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[\Override]
    public function check(
        string $responseValue,
    ): true|string|array {
        if (!$this->isEnabled()) {
            return true;
        }

        $response = $this->client->request('POST', $this->verifyUrl, [
            'query' => [
                'secret' => $this->privateKey,
                'response' => $responseValue,
            ],
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $jsonResponse = json_decode($response->getContent(false), true);

        return (isset($jsonResponse['success']) && true === $jsonResponse['success']) ?
            (true) :
            ($jsonResponse['error-codes']);
    }

    #[\Override]
    public function getFieldName(): string
    {
        return 'g-recaptcha-response';
    }

    #[\Override]
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * {% block recaptcha_widget -%}
     *     <div class="g-recaptcha" data-sitekey="{{ configs.publicKey }}"></div>
     * {%- endblock recaptcha_widget %}.
     */
    #[\Override]
    public function getFormWidgetName(): string
    {
        return 'recaptcha';
    }
}
