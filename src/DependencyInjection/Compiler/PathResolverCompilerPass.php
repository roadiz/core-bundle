<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\CoreBundle\Routing\ChainResourcePathResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PathResolverCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has(ChainResourcePathResolver::class)) {
            $definition = $container->findDefinition(ChainResourcePathResolver::class);
            $taggedServices = $container->findTaggedServiceIds(
                'roadiz_core.path_resolver'
            );
            foreach ($taggedServices as $id => $tags) {
                $definition->addMethodCall(
                    'addPathResolver',
                    [new Reference($id)]
                );
            }
        }
    }
}
