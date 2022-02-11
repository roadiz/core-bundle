<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection;

use RZ\Roadiz\CoreBundle\Controller\DefaultNodeSourceController;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const INHERITANCE_TYPE_JOINED = 'joined';
    public const INHERITANCE_TYPE_SINGLE_TABLE = 'single_table';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('roadiz_core');
        $root = $builder->getRootNode();

        $root->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('appNamespace')
                ->defaultValue('roadiz_app')
            ->end()
            ->scalarNode('staticDomainName')
                ->defaultValue(null)
            ->end()
            ->scalarNode('defaultNodeSourceController')
                ->defaultValue(DefaultNodeSourceController::class)
            ->end()
            ->booleanNode('useNativeJsonColumnType')
                ->defaultValue(true)
            ->end()
            ->arrayNode('security')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('private_key_name')
                        ->defaultValue('default')
                        ->info('Asymmetric cryptographic key name.')
                    ->end()
                ->end()
            ->end()
            ->append($this->addSolrNode())
            ->append($this->addInheritanceNode())
            ->append($this->addReverseProxyCacheNode())
            ->append($this->addMediasNode())
            ->append($this->addOpenIdNode())
        ;
        return $builder;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addInheritanceNode()
    {
        $builder = new TreeBuilder('inheritance');
        $node = $builder->getRootNode();
        $node->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('type')
                    ->defaultValue(static::INHERITANCE_TYPE_SINGLE_TABLE)
                    ->info(<<<EOD
Doctrine inheritance strategy for creating NodesSources
classes table(s). BE CAREFUL, if you change this
setting after filling content in your website, all
node-sources data will be lost.
EOD
                    )
                    ->validate()
                    ->ifNotInArray([
                        static::INHERITANCE_TYPE_JOINED,
                        static::INHERITANCE_TYPE_SINGLE_TABLE
                    ])
                    ->thenInvalid('The %s inheritance type is not supported ("joined", "single_table" are accepted).')
                ->end()
            ->end()
        ;
        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addOpenIdNode()
    {
        $builder = new TreeBuilder('open_id');
        $node = $builder->getRootNode();
        $node->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('verify_user_info')
                    ->defaultTrue()
                    ->info(<<<EOD
Verify User info in JWT at each login
EOD
                    )
                ->end()
                ->scalarNode('discovery_url')
                    ->defaultValue('')
                    ->info(<<<EOD
Standard OpenID autodiscovery URL, required to enable OpenId login in Roadiz CMS.
EOD
                    )
                ->end()
                ->scalarNode('hosted_domain')
                    ->defaultNull()
                    ->info(<<<EOD
For public identity providers (such as Google), restrict users emails by their domain.
EOD
                    )
                ->end()
                ->scalarNode('oauth_client_id')
                    ->defaultNull()
                    ->info(<<<EOD
OpenID identity provider OAuth2 client ID
EOD
                    )
                ->end()
                ->scalarNode('oauth_client_secret')
                    ->defaultNull()
                    ->info(<<<EOD
OpenID identity provider OAuth2 client secret
EOD
                    )
                ->end()
                ->scalarNode('openid_username_claim')
                    ->defaultValue('email')
                    ->info(<<<EOD
OpenID identity provider identifier claim field
EOD
                    )
                ->end()
                ->arrayNode('scopes')
                    ->prototype('scalar')
                    ->defaultValue([])
                    ->info(<<<EOD
Scopes requested during OpenId authentication process.
EOD
                    )
                    ->end()
                ->end()
                ->arrayNode('granted_roles')
                    ->prototype('scalar')
                    ->defaultValue(['ROLE_USER'])
                    ->info(<<<EOD
Roles granted to user logged in with OpenId authentication process.
EOD
                    )
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addMediasNode()
    {
        $builder = new TreeBuilder('medias');
        $node = $builder->getRootNode();
        $node->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('unsplash_client_id')->defaultNull()->end()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addSolrNode()
    {
        $builder = new TreeBuilder('solr');
        $node = $builder->getRootNode();

        $node->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('timeout')->defaultValue(3)->end()
                ->arrayNode('endpoints')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->defaultValue('127.0.0.1')->end()
                            ->scalarNode('username')->end()
                            ->scalarNode('password')->end()
                            ->scalarNode('core')->isRequired()->end()
                            ->enumNode('scheme')
                                ->values(['http', 'https'])
                                ->defaultValue('http')
                            ->end()
                            ->scalarNode('port')->defaultValue(8983)->end()
                            ->scalarNode('path')->defaultValue('/')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addReverseProxyCacheNode()
    {
        $builder = new TreeBuilder('reverseProxyCache');
        $node = $builder->getRootNode();
        $node->children()
                ->arrayNode('frontend')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('host')
                            ->isRequired()
                            ->defaultValue('localhost')
                        ->end()
                        ->scalarNode('domainName')
                            ->isRequired()
                            ->defaultValue('localhost')
                        ->end()
                        ->scalarNode('timeout')->defaultValue(3)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cloudflare')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('version')
                            ->defaultValue('v4')
                        ->end()
                        ->scalarNode('zone')
                            ->isRequired()
                        ->end()
                        ->scalarNode('bearer')->end()
                        ->scalarNode('email')->end()
                        ->scalarNode('key')->end()
                        ->scalarNode('timeout')
                            ->defaultValue(3)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
