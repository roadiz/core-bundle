<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi;
use RZ\TreeWalker\WalkerContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Stopwatch\Stopwatch;

final class NodeSourceWalkerContextFactory implements WalkerContextFactoryInterface
{
    private Stopwatch $stopwatch;
    private NodeTypes $nodeTypesBag;
    private NodeSourceApi $nodeSourceApi;
    private RequestStack $requestStack;
    private ManagerRegistry $managerRegistry;

    /**
     * @param Stopwatch $stopwatch
     * @param NodeTypes $nodeTypesBag
     * @param NodeSourceApi $nodeSourceApi
     * @param RequestStack $requestStack
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(
        Stopwatch $stopwatch,
        NodeTypes $nodeTypesBag,
        NodeSourceApi $nodeSourceApi,
        RequestStack $requestStack,
        ManagerRegistry $managerRegistry
    ) {
        $this->stopwatch = $stopwatch;
        $this->nodeTypesBag = $nodeTypesBag;
        $this->nodeSourceApi = $nodeSourceApi;
        $this->requestStack = $requestStack;
        $this->managerRegistry = $managerRegistry;
    }

    public function createWalkerContext(): WalkerContextInterface
    {
        return new NodeSourceWalkerContext(
            $this->stopwatch,
            $this->nodeTypesBag,
            $this->nodeSourceApi,
            $this->requestStack,
            $this->managerRegistry
        );
    }
}
