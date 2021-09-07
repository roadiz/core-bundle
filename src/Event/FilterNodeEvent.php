<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package RZ\Roadiz\CoreBundle\Event
 */
abstract class FilterNodeEvent extends Event
{
    /**
     * @var Node
     */
    protected $node;

    /**
     * @param Node $node
     */
    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    /**
     * @return Node
     */
    public function getNode(): Node
    {
        return $this->node;
    }
}
