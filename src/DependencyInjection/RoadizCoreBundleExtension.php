<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection;

use RZ\Roadiz\CoreBundle\Cache\ReverseProxyCache;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RoadizCoreBundleExtension extends Extension
{
    public function getAlias()
    {
        return 'roadiz_core';
    }

    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/../config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('roadiz_core.inheritance_type', $config['inheritance']['type']);
        $container->setParameter('roadiz_core.static_domain_name', $config['staticDomainName'] ?? '');

        date_default_timezone_set($config['timezone']);

        /*
         * Themes config
         */
        $frontendThemes = [];
        foreach ($config['themes'] as $index => $themeConfig) {
            $theme = new Theme();
            $theme->setId($index);
            $theme->setAvailable(true);
            $theme->setClassName($themeConfig['classname']);
            $theme->setBackendTheme(false);
            $theme->setStaticTheme(false);
            $theme->setHostname($themeConfig['hostname']);
            $theme->setRoutePrefix($themeConfig['routePrefix']);
            $frontendThemes[] = $theme;
        }
        $container->setParameter('roadiz_core.themes', $frontendThemes);

        /*
         * Reverse Proxy cache config
         */
        $reverseProxyCacheFrontends = [];
        foreach ($config['reverseProxyCache']['frontend'] as $name => $frontend) {
            $reverseProxyCacheFrontends[] = new ReverseProxyCache(
                $name,
                $frontend['host'],
                $frontend['domainName'],
                $frontend['timeout'],
            );
        }
        $container->setParameter('roadiz_core.reverse_proxy_cache.frontends', $reverseProxyCacheFrontends);
    }
}
