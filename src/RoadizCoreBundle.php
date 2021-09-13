<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle;

use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\CommonMarkCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\DocumentRendererCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RoadizCoreBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CommonMarkCompilerPass());
        $container->addCompilerPass(new DocumentRendererCompilerPass());
    }
}
