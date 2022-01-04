<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Controller;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use RZ\Roadiz\CoreBundle\Routing\PathResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

final class GetNodesSourcesByPathController extends AbstractController
{
    private RequestStack $requestStack;
    private PathResolverInterface $pathResolver;

    /**
     * @param RequestStack $requestStack
     * @param PathResolverInterface $pathResolver
     */
    public function __construct(RequestStack $requestStack, PathResolverInterface $pathResolver)
    {
        $this->requestStack = $requestStack;
        $this->pathResolver = $pathResolver;
    }

    public function __invoke(): ?NodesSources
    {
        if (
            null === $this->requestStack->getMainRequest() ||
            empty($this->requestStack->getMainRequest()->query->get('path'))
        ) {
            throw new InvalidArgumentException('path query parameter is mandatory');
        }
        return $this->normalizeNodesSourcesPath(
            (string) $this->requestStack->getMainRequest()->query->get('path')
        );
    }
    /**
     * @param string $path
     * @return NodesSources|null Returns nodes-sources or null if no NS found for path to filter all results.
     */
    protected function normalizeNodesSourcesPath(string $path): ?NodesSources
    {
        $resourceInfo = $this->pathResolver->resolvePath($path, ['html', 'json'], true);
        $resource = $resourceInfo->getResource();

        /*
         * Normalize redirected node-sources
         */
        if (
            $resource instanceof Redirection &&
            null !== $resource->getRedirectNodeSource()
        ) {
            return $resource->getRedirectNodeSource();
        }
        /*
         * Or plain node-source
         */
        if ($resource instanceof NodesSources) {
            return $resource;
        }
        return null;
    }
}
