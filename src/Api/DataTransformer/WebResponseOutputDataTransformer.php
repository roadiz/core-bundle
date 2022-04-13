<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsFactoryInterface;
use RZ\Roadiz\CoreBundle\Api\Model\NodesSourcesHeadFactory;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponse;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\AutoChildrenNodeSourceWalker;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\TreeWalker\WalkerContextInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class WebResponseOutputDataTransformer implements WebResponseDataTransformerInterface
{
    private NodesSourcesHeadFactory $nodesSourcesHeadFactory;
    private BreadcrumbsFactoryInterface $breadcrumbsFactory;
    private WalkerContextInterface $walkerContext;
    private CacheItemPoolInterface $cacheItemPool;
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @param NodesSourcesHeadFactory $nodesSourcesHeadFactory
     * @param BreadcrumbsFactoryInterface $breadcrumbsFactory
     * @param WalkerContextInterface $walkerContext
     * @param CacheItemPoolInterface $cacheItemPool
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        NodesSourcesHeadFactory $nodesSourcesHeadFactory,
        BreadcrumbsFactoryInterface $breadcrumbsFactory,
        WalkerContextInterface $walkerContext,
        CacheItemPoolInterface $cacheItemPool,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->nodesSourcesHeadFactory = $nodesSourcesHeadFactory;
        $this->breadcrumbsFactory = $breadcrumbsFactory;
        $this->walkerContext = $walkerContext;
        $this->cacheItemPool = $cacheItemPool;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = []): ?WebResponseInterface
    {
        if (!$data instanceof PersistableInterface) {
            throw new \InvalidArgumentException(
                'Data to transform must be instance of ' .
                PersistableInterface::class
            );
        }
        $output = new WebResponse();
        $output->item = $data;
        if ($data instanceof NodesSources) {
            $output->path = $this->urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                RouteObjectInterface::ROUTE_OBJECT => $data
            ], UrlGeneratorInterface::ABSOLUTE_PATH);
            $output->head = $this->nodesSourcesHeadFactory->createForNodeSource($data);
            $output->breadcrumbs = $this->breadcrumbsFactory->create($data);
            $output->blocks = AutoChildrenNodeSourceWalker::build(
                $data,
                $this->walkerContext,
                5,
                $this->cacheItemPool
            )->getChildren();
        }
        if ($data instanceof TranslationInterface) {
            $output->head = $this->nodesSourcesHeadFactory->createForTranslation($data);
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return WebResponseInterface::class === $to && $data instanceof PersistableInterface;
    }
}
