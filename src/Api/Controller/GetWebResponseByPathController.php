<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Controller;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\DataTransformer\WebResponseDataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use RZ\Roadiz\CoreBundle\Routing\PathResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

final class GetWebResponseByPathController extends AbstractController
{
    private RequestStack $requestStack;
    private PathResolverInterface $pathResolver;
    private WebResponseDataTransformerInterface $webResponseDataTransformer;

    /**
     * @param RequestStack $requestStack
     * @param PathResolverInterface $pathResolver
     * @param WebResponseDataTransformerInterface $webResponseDataTransformer
     */
    public function __construct(
        RequestStack $requestStack,
        PathResolverInterface $pathResolver,
        WebResponseDataTransformerInterface $webResponseDataTransformer
    ) {
        $this->requestStack = $requestStack;
        $this->pathResolver = $pathResolver;
        $this->webResponseDataTransformer = $webResponseDataTransformer;
    }

    public function __invoke(): ?WebResponseInterface
    {
        if (
            null === $this->requestStack->getMainRequest() ||
            empty($this->requestStack->getMainRequest()->query->get('path'))
        ) {
            throw new InvalidArgumentException('path query parameter is mandatory');
        }
        $resource = $this->normalizeNodesSourcesPath(
            (string) $this->requestStack->getMainRequest()->query->get('path')
        );
        $this->requestStack->getMainRequest()->attributes->set('data', $resource);
        return $this->webResponseDataTransformer->transform($resource, WebResponseInterface::class);
    }

    /**
     * @param string $path
     * @return PersistableInterface|null
     */
    protected function normalizeNodesSourcesPath(string $path): ?PersistableInterface
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
         * Or plain entity
         */
        return $resource;
    }
}
