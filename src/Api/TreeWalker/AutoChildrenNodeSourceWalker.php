<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition\MultiTypeChildrenDefinition;
use RZ\TreeWalker\AbstractWalker;
use RZ\TreeWalker\Definition\ZeroChildrenDefinition;

/**
 * AutoChildrenNodeSourceWalker automatically creates Walker definitions based on your Node-types
 * children fields default values.
 *
 * Override this class to customize definitions
 */
class AutoChildrenNodeSourceWalker extends AbstractWalker
{
    protected function initializeDefinitions(): void
    {
        if ($this->isRoot()) {
            $context = $this->getContext();
            if ($context instanceof NodeSourceWalkerContext) {
                /** @var NodeTypeInterface $nodeType */
                foreach ($context->getNodeTypesBag()->all() as $nodeType) {
                    $this->addDefinition(
                        $nodeType->getSourceEntityFullQualifiedClassName(),
                        $this->createDefinitionForNodeType($nodeType)
                    );
                }

                $this->initializeAdditionalDefinitions();
            }
        }
    }

    protected function initializeAdditionalDefinitions(): void
    {
        // override this for custom tree-walker definitions
    }

    /**
     * @param NodeTypeInterface $nodeType
     * @return callable
     */
    protected function createDefinitionForNodeType(NodeTypeInterface $nodeType): callable
    {
        $childrenNodeTypes = $this->getChildrenNodeTypeList($nodeType);
        if (count($childrenNodeTypes) > 0) {
            return new MultiTypeChildrenDefinition($this->getContext(), $childrenNodeTypes);
        }

        return new ZeroChildrenDefinition($this->getContext());
    }

    /**
     * @param NodeTypeFieldInterface $field
     * @return array<string>
     */
    protected function getNodeTypeList(NodeTypeFieldInterface $field): array
    {
        $nodeTypesNames = array_map('trim', explode(',', $field->getDefaultValues() ?? ''));
        return array_filter($nodeTypesNames);
    }

    /**
     * @param NodeTypeInterface $nodeType
     * @return array<string>
     */
    protected function getChildrenNodeTypeList(NodeTypeInterface $nodeType): array
    {
        $context = $this->getContext();
        $cacheKey = 'autochildren_' . $nodeType->getName();

        if ($context instanceof NodeSourceWalkerContext) {
            $cacheItem = $context->getCacheAdapter()->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        }

        $childrenTypes = [];
        $childrenFields = $nodeType->getFields()->filter(function (NodeTypeFieldInterface $field) {
            return $field->isChildrenNodes() && null !== $field->getDefaultValues();
        });
        if ($childrenFields->count() > 0) {
            /** @var NodeTypeFieldInterface $field */
            foreach ($childrenFields as $field) {
                $childrenTypes = array_merge($childrenTypes, $this->getNodeTypeList($field));
            }
            $childrenTypes = array_filter(array_unique($childrenTypes));
        }

        if ($context instanceof NodeSourceWalkerContext) {
            $cacheItem = $context->getCacheAdapter()->getItem($cacheKey);
            $cacheItem->set($childrenTypes);
            $context->getCacheAdapter()->save($cacheItem);
        }

        return $childrenTypes;
    }
}
