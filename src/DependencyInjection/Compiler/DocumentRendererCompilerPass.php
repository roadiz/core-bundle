<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\Document\Renderer\ChainRenderer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DocumentRendererCompilerPass implements CompilerPassInterface
{

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has(ChainRenderer::class)) {
            $definition = $container->findDefinition(ChainRenderer::class);
            $taggedServices = $container->findTaggedServiceIds(
                'roadiz_core.document_renderer'
            );
            foreach ($taggedServices as $id => $tags) {
                $definition->addMethodCall(
                    'addRenderer',
                    array(new Reference($id))
                );
            }
        }
    }
}
