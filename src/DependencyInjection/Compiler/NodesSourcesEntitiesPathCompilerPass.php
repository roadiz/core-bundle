<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NodesSourcesEntitiesPathCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $projectDir = $container->getParameter('kernel.project_dir');
        $container->setParameter('roadiz_core.generated_entities_dir', $projectDir . '/src/GeneratedEntity');
    }
}
