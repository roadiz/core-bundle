<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Theme\ThemeResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * UrlMatcher which tries to grab Node and Translation
 * information for a route.
 */
class NodeUrlMatcher extends DynamicUrlMatcher
{
    protected PathResolverInterface $pathResolver;
    /**
     * @var class-string
     */
    private string $defaultControllerClass;

    /**
     * @return array
     */
    public function getSupportedFormatExtensions(): array
    {
        return ['xml', 'json', 'pdf', 'html'];
    }

    /**
     * @return string
     */
    public function getDefaultSupportedFormatExtension(): string
    {
        return 'html';
    }

    /**
     * @param PathResolverInterface $pathResolver
     * @param RequestContext $context
     * @param ThemeResolverInterface $themeResolver
     * @param PreviewResolverInterface $previewResolver
     * @param Stopwatch $stopwatch
     * @param LoggerInterface $logger
     * @param class-string $defaultControllerClass
     */
    public function __construct(
        PathResolverInterface $pathResolver,
        RequestContext $context,
        ThemeResolverInterface $themeResolver,
        PreviewResolverInterface $previewResolver,
        Stopwatch $stopwatch,
        LoggerInterface $logger,
        string $defaultControllerClass
    ) {
        parent::__construct($context, $themeResolver, $previewResolver, $stopwatch, $logger);
        $this->pathResolver = $pathResolver;
        $this->defaultControllerClass = $defaultControllerClass;
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        $this->stopwatch->start('findTheme');
        $this->theme = $this->themeResolver->findTheme($this->context->getHost());
        $this->stopwatch->stop('findTheme');

        $decodedUrl = rawurldecode($pathinfo);
        /*
         * Try nodes routes
         */
        return $this->matchNode($decodedUrl);
    }

    /**
     * @param string $decodedUrl
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function matchNode(string $decodedUrl): array
    {
        $resourceInfo = $this->pathResolver->resolvePath($decodedUrl, $this->getSupportedFormatExtensions());
        $nodeSource = $resourceInfo->getResource();

        if ($nodeSource instanceof NodesSources && !$nodeSource->getNode()->isHome()) {
            $translation = $nodeSource->getTranslation();
            $nodeRouteHelper = new NodeRouteHelper(
                $nodeSource->getNode(),
                $this->theme,
                $this->previewResolver,
                $this->logger,
                $this->defaultControllerClass
            );

            if (!$this->previewResolver->isPreview() && !$translation->isAvailable()) {
                throw new ResourceNotFoundException();
            }

            if (false === $nodeRouteHelper->isViewable()) {
                throw new ResourceNotFoundException();
            }

            return [
                '_controller' => $nodeRouteHelper->getController() . '::' . $nodeRouteHelper->getMethod(),
                '_locale' => $resourceInfo->getLocale(),
                '_route' => RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                '_format' => $resourceInfo->getFormat(),
                'node' => $nodeSource->getNode(),
                'nodeSource' => $nodeSource,
                RouteObjectInterface::ROUTE_OBJECT => $resourceInfo->getResource(),
                'translation' => $resourceInfo->getTranslation(),
                'theme' => $this->theme,
            ];
        }
        throw new ResourceNotFoundException();
    }
}
