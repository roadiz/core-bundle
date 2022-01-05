<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsFactoryInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\WebResponseOutput;
use RZ\Roadiz\CoreBundle\Api\Model\NodesSourcesHeadFactory;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\AutoChildrenNodeSourceWalker;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\TreeWalker\WalkerContextInterface;

final class WebResponseOutputDataTransformer implements WebResponseDataTransformerInterface
{
    private NodesSourcesHeadFactory $nodesSourcesHeadFactory;
    private BreadcrumbsFactoryInterface $breadcrumbsFactory;
    private WalkerContextInterface $walkerContext;
    private CacheItemPoolInterface $cacheItemPool;

    /**
     * @param NodesSourcesHeadFactory $nodesSourcesHeadFactory
     * @param BreadcrumbsFactoryInterface $breadcrumbsFactory
     * @param WalkerContextInterface $walkerContext
     * @param CacheItemPoolInterface $cacheItemPool
     */
    public function __construct(
        NodesSourcesHeadFactory $nodesSourcesHeadFactory,
        BreadcrumbsFactoryInterface $breadcrumbsFactory,
        WalkerContextInterface $walkerContext,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->nodesSourcesHeadFactory = $nodesSourcesHeadFactory;
        $this->breadcrumbsFactory = $breadcrumbsFactory;
        $this->walkerContext = $walkerContext;
        $this->cacheItemPool = $cacheItemPool;
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
        $output = new WebResponseOutput();
        $output->item = $data;
        if ($data instanceof NodesSources) {
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
