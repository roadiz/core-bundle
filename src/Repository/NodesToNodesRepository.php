<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\NodesToNodes>
 */
class NodesToNodesRepository extends EntityRepository
{
    /**
     * @param Node          $node
     * @param NodeTypeField $field
     *
     * @return integer
     */
    public function getLatestPosition(Node $node, NodeTypeField $field)
    {
        $query = $this->_em->createQuery('
            SELECT MAX(ntn.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesToNodes ntn
            WHERE ntn.nodeA = :nodeA AND ntn.field = :field')
                    ->setParameter('nodeA', $node)
                    ->setParameter('field', $field);

        return (int) $query->getSingleScalarResult();
    }
}
