<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

use ArrayIterator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\NodeSourceWalkerContext;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\TreeWalker\Definition\ContextualDefinitionTrait;
use RZ\TreeWalker\WalkerContextInterface;

final class ReachableNodeSourceDefinition
{
    use ContextualDefinitionTrait;

    public function __construct(
        private readonly WalkerContextInterface $context,
        private readonly bool $onlyVisible = true
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(NodesSources $source): array
    {
        if (!($this->context instanceof NodeSourceWalkerContext)) {
            throw new \InvalidArgumentException('Context should be instance of ' . NodeSourceWalkerContext::class);
        }

        $this->context->getStopwatch()->start(self::class);
        $criteria = [
            'node.parent' => $source->getNode(),
            'translation' => $source->getTranslation(),
            'node.nodeType.reachable' => true,
        ];
        if ($this->onlyVisible) {
            $criteria['node.visible'] = true;
        }
        $children = $this->context->getNodeSourceApi()->getBy($criteria, [
            'node.position' => 'ASC',
        ]);
        $this->context->getStopwatch()->stop(self::class);

        if ($children instanceof Paginator) {
            $iterator = $children->getIterator();
            if ($iterator instanceof ArrayIterator) {
                return $iterator->getArrayCopy();
            }
            // @phpstan-ignore-next-line
            return iterator_to_array($iterator);
        }
        return $children;
    }
}
