<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use RZ\Roadiz\CoreBundle\Theme\ThemeResolverInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

final class ThemeRoutesLoader extends Loader
{
    private bool $isLoaded = false;
    private ThemeResolverInterface $themeResolver;

    /**
     * @param ThemeResolverInterface $themeResolver
     */
    public function __construct(ThemeResolverInterface $themeResolver)
    {
        $this->themeResolver = $themeResolver;
    }

    public function load($resource, string $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routeCollection = new RouteCollection();
        $frontendThemes = $this->themeResolver->getFrontendThemes();
        foreach ($frontendThemes as $theme) {
            $feClass = $theme->getClassName();
            /** @var RouteCollection $feCollection */
            $feCollection = call_user_func([$feClass, 'getRoutes']);
            /** @var RouteCollection $feBackendCollection */
            $feBackendCollection = call_user_func([$feClass, 'getBackendRoutes']);

            if ($feCollection !== null) {
                // set host pattern if defined
                if ($theme->getHostname() != '*' && $theme->getHostname() != '') {
                    $feCollection->setHost($theme->getHostname());
                }
                /*
                 * Add a global prefix on theme static routes
                 */
                if ($theme->getRoutePrefix() != '') {
                    $feCollection->addPrefix($theme->getRoutePrefix());
                }
                $routeCollection->addCollection($feCollection);
            }

            if ($feBackendCollection !== null) {
                /*
                 * Do not prefix or hostname admin routes.
                 */
                $routeCollection->addCollection($feBackendCollection);
            }
        }

        $this->isLoaded = true;

        return $routeCollection;
    }

    public function supports($resource, string $type = null)
    {
        return 'themes' === $type;
    }
}
