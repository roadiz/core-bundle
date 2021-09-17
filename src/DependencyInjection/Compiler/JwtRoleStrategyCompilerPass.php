<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\OpenId\Authentication\Provider\ChainJwtRoleStrategy;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class JwtRoleStrategyCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has(ChainJwtRoleStrategy::class)) {
            $definition = $container->findDefinition(ChainJwtRoleStrategy::class);
            $taggedServices = $container->findTaggedServiceIds(
                'roadiz_core.jwt_role_strategy'
            );
            $taggedServicesReferences = [];
            foreach ($taggedServices as $id => $tags) {
                $taggedServicesReferences = new Reference($id);
            }
            $definition->setArgument(
                '$strategies',
                [$taggedServicesReferences]
            );
        }
    }
}
