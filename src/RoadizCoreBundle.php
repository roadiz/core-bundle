<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle;

use RZ\Roadiz\CoreBundle\DependencyInjection\RoadizCoreBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RoadizCoreBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    protected function getContainerExtensionClass()
    {
        return RoadizCoreBundleExtension::class;
    }
}
