<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * UrlMatcher which tries to grab Node and Translation
 * information for a route.
 */
final class NodeUrlMatcher extends DynamicUrlMatcher implements NodeUrlMatcherInterface
{
    protected ?Theme $theme;
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
     * @param PreviewResolverInterface $previewResolver
     * @param Stopwatch $stopwatch
     * @param LoggerInterface $logger
     * @param class-string $defaultControllerClass
     */
    public function __construct(
        PathResolverInterface $pathResolver,
        RequestContext $context,
        PreviewResolverInterface $previewResolver,
        Stopwatch $stopwatch,
        LoggerInterface $logger,
        string $defaultControllerClass
    ) {
        parent::__construct($context, $previewResolver, $stopwatch, $logger);
        $this->pathResolver = $pathResolver;
        $this->defaultControllerClass = $defaultControllerClass;
    }

    /**
     * @return Theme|null
     */
    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    /**
     * @param Theme|null $theme
     */
    public function setTheme(?Theme $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * {@inheritdoc}
     */
    public function match(string $pathinfo)
    {
        $decodedUrl = rawurldecode($pathinfo);
        /*
         * Try nodes routes
         */
        return $this->matchNode($decodedUrl);
    }

    protected function getNodeRouteHelper(NodesSources $nodeSource): NodeRouteHelper
    {
        return new NodeRouteHelper(
            $nodeSource->getNode(),
            $this->getTheme(),
            $this->previewResolver,
            $this->logger,
            $this->defaultControllerClass
        );
    }

    /**
     * @param string $decodedUrl
     *
     * @return array
     * @throws \ReflectionException
     */
    public function matchNode(string $decodedUrl): array
    {
        $resourceInfo = $this->pathResolver->resolvePath($decodedUrl, $this->getSupportedFormatExtensions());
        $nodeSource = $resourceInfo->getResource();

        if ($nodeSource instanceof NodesSources && !$nodeSource->getNode()->isHome()) {
            $translation = $nodeSource->getTranslation();
            $nodeRouteHelper = $this->getNodeRouteHelper($nodeSource);

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
