<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const INHERITANCE_TYPE_JOINED = 'joined';
    public const INHERITANCE_TYPE_SINGLE_TABLE = 'single_table';

    public function getConfigTreeBuilder()
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
            ->scalarNode('timezone')
                ->defaultValue('Europe/Paris')
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
            ->append($this->addAssetsNode())
            ->append($this->addSolrNode())
            ->append($this->addThemesNode())
            ->append($this->addInheritanceNode())
            ->append($this->addReverseProxyCacheNode())
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
                    ->defaultValue(static::INHERITANCE_TYPE_JOINED)
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
    protected function addAssetsNode()
    {
        $builder = new TreeBuilder('assetsProcessing');
        $node = $builder->getRootNode();
        $node->addDefaultsIfNotSet()
            ->children()
                ->enumNode('driver')
                    ->values(['gd', 'imagick'])
                    ->defaultValue('gd')
                    ->info('GD does not support TIFF and PSD formats, but iMagick must be installed')
                ->end()
                ->integerNode('defaultQuality')
                    ->min(10)
                    ->max(100)
                    ->defaultValue(95)
                ->end()
                ->integerNode('maxPixelSize')
                    ->min(600)
                    ->defaultValue(2500)
                    ->info('Pixel width limit after Roadiz should create a smaller copy')
                ->end()
                ->scalarNode('jpegoptimPath')->defaultNull()->end()
                ->scalarNode('pngquantPath')->defaultNull()->end()
            ->arrayNode('subscribers')
                ->prototype('array')
                    ->children()
                        ->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
                        ->arrayNode('args')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
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

        $node->children()
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

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addThemesNode()
    {
        $builder = new TreeBuilder('themes');
        $node = $builder->getRootNode();

        $node
            ->defaultValue([])
            ->prototype('array')
            ->children()
                ->scalarNode('classname')
                    ->info('Full qualified theme class (this must start with \ character and ends with App suffix)')
                    ->isRequired()
                    ->validate()
                        ->ifTrue(function (string $s) {
                            return preg_match('/^\\\[a-zA-Z\\\]+App$/', trim($s)) !== 1 || !class_exists($s);
                        })
                        ->thenInvalid('Theme class does not exist or classname is invalid: must start with \ character and ends with App suffix.')
                    ->end()
                ->end()
                ->scalarNode('hostname')
                    ->defaultValue('*')
                ->end()
                ->scalarNode('routePrefix')
                    ->defaultValue('')
                ->end()
            ->end()
            ->end();

        return $node;
    }
}
