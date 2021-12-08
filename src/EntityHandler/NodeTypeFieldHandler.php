<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\Core\Handlers\AbstractHandler;

/**
 * Handle operations with node-type fields entities.
 */
class NodeTypeFieldHandler extends AbstractHandler
{
    private HandlerFactory $handlerFactory;
    private ?NodeTypeField $nodeTypeField = null;

    public function getNodeTypeField(): NodeTypeField
    {
        if (null === $this->nodeTypeField) {
            throw new \BadMethodCallException('NodeTypeField is null');
        }
        return $this->nodeTypeField;
    }

    /**
     * @param NodeTypeField $nodeTypeField
     * @return $this
     */
    public function setNodeTypeField(NodeTypeField $nodeTypeField)
    {
        $this->nodeTypeField = $nodeTypeField;
        return $this;
    }

    /**
     * Create a new node-type-field handler with node-type-field to handle.
     *
     * @param ObjectManager $objectManager
     * @param HandlerFactory $handlerFactory
     */
    public function __construct(ObjectManager $objectManager, HandlerFactory $handlerFactory)
    {
        parent::__construct($objectManager);
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * Clean position for current node siblings.
     *
     * @param bool $setPosition
     * @return float Return the next position after the **last** node
     */
    public function cleanPositions(bool $setPosition = false): float
    {
        if ($this->nodeTypeField->getNodeType() !== null) {
            /** @var NodeTypeHandler $nodeTypeHandler */
            $nodeTypeHandler = $this->handlerFactory->getHandler($this->nodeTypeField->getNodeType());
            return $nodeTypeHandler->cleanPositions();
        }

        return 1;
    }
}
