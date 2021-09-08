<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

final class ReverseProxyCacheLocator
{
    /**
     * @var ReverseProxyCache[]
     */
    private array $frontends;

    /**
     * @param ReverseProxyCache[] $frontends
     */
    public function __construct(array $frontends)
    {
        $this->frontends = $frontends;
    }

    /**
     * @return ReverseProxyCache[]
     */
    public function getFrontends(): array
    {
        return $this->frontends;
    }
}
