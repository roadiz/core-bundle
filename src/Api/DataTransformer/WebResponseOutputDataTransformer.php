<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsFactoryInterface;
use RZ\Roadiz\CoreBundle\Api\Model\NodesSourcesHeadFactory;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponse;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\AutoChildrenNodeSourceWalker;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\TreeWalker\WalkerContextInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

final class WebResponseOutputDataTransformer implements WebResponseDataTransformerInterface
{
    use BlocksAwareWebResponseOutputDataTransformerTrait;
    use RealmsAwareWebResponseOutputDataTransformerTrait;

    private NodesSourcesHeadFactory $nodesSourcesHeadFactory;
    private BreadcrumbsFactoryInterface $breadcrumbsFactory;
    private WalkerContextInterface $walkerContext;
    private CacheItemPoolInterface $cacheItemPool;
    private UrlGeneratorInterface $urlGenerator;
    private ManagerRegistry $managerRegistry;
    private Security $security;

    public function __construct(
        NodesSourcesHeadFactory $nodesSourcesHeadFactory,
        BreadcrumbsFactoryInterface $breadcrumbsFactory,
        WalkerContextInterface $walkerContext,
        CacheItemPoolInterface $cacheItemPool,
        UrlGeneratorInterface $urlGenerator,
        ManagerRegistry $managerRegistry,
        Security $security
    ) {
        $this->nodesSourcesHeadFactory = $nodesSourcesHeadFactory;
        $this->breadcrumbsFactory = $breadcrumbsFactory;
        $this->walkerContext = $walkerContext;
        $this->cacheItemPool = $cacheItemPool;
        $this->urlGenerator = $urlGenerator;
        $this->managerRegistry = $managerRegistry;
        $this->security = $security;
    }

    protected function getWalkerContext(): WalkerContextInterface
    {
        return $this->walkerContext;
    }

    protected function getCacheItemPool(): CacheItemPoolInterface
    {
        return $this->cacheItemPool;
    }

    protected function getChildrenNodeSourceWalkerMaxLevel(): int
    {
        return 5;
    }

    protected function getChildrenNodeSourceWalkerClassname(): string
    {
        return AutoChildrenNodeSourceWalker::class;
    }

    protected function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    protected function getSecurity(): Security
    {
        return $this->security;
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
            $this->injectRealms($output, $data);
            $this->injectBlocks($output, $data);

            $output->path = $this->urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                RouteObjectInterface::ROUTE_OBJECT => $data
            ], UrlGeneratorInterface::ABSOLUTE_PATH);
            $output->head = $this->nodesSourcesHeadFactory->createForNodeSource($data);
            $output->breadcrumbs = $this->breadcrumbsFactory->create($data);
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
