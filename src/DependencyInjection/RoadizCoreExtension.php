<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection;

use RZ\Roadiz\CoreBundle\Cache\ReverseProxyCache;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RoadizCoreExtension extends Extension
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

        $container->setParameter('roadiz_core.app_namespace', $config['appNamespace']);
        $container->setParameter('roadiz_core.inheritance_type', $config['inheritance']['type']);
        $container->setParameter('roadiz_core.static_domain_name', $config['staticDomainName'] ?? '');
        $container->setParameter('roadiz_core.private_key_name', $config['security']['private_key_name']);

        date_default_timezone_set($config['timezone']);

        /*
         * Assets config
         */
        $container->setParameter(
            'roadiz_core.assets_processing.max_pixel_size',
            $config['assetsProcessing']['maxPixelSize'] ?? 2500
        );
        $container->setParameter(
            'roadiz_core.assets_processing.driver',
            $config['assetsProcessing']['driver'] ?? 'gd'
        );
        $container->setParameter(
            'roadiz_core.assets_processing.default_quality',
            $config['assetsProcessing']['defaultQuality'] ?? 90
        );

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
