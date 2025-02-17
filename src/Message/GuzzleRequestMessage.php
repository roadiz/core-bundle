<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

use Psr\Http\Message\RequestInterface;

final class GuzzleRequestMessage implements AsyncMessage, HttpRequestMessage
{
    private array $options;

    /**
     * @param RequestInterface $request
     * @param array $options
     */
    public function __construct(
        private readonly RequestInterface $request,
        array $options = []
    ) {
        $this->options = array_merge([
            'debug' => false,
            'timeout' => 3
        ], $options);
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
