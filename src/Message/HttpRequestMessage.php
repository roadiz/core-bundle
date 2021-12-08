<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

use Psr\Http\Message\RequestInterface;

interface HttpRequestMessage
{
    public function getRequest(): RequestInterface;
    public function getOptions(): array;
}
