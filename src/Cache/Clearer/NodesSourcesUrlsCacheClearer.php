<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache\Clearer;

use Symfony\Component\Cache\Adapter\AdapterInterface;

final class NodesSourcesUrlsCacheClearer extends FileClearer
{
    private AdapterInterface $cacheProvider;

    public function __construct(AdapterInterface $cacheProvider)
    {
        parent::__construct('');
        $this->cacheProvider = $cacheProvider;
    }

    public function clear(): bool
    {
        $this->output .= 'Node-sources URLs cache: ';

        if ($this->cacheProvider->clear()) {
            $this->output .= 'cleared';
            return true;
        }
        return false;
    }
}
