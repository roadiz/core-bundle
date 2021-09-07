<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\NodesCustomForms>
 */
class NodesCustomFormsRepository extends EntityRepository
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
            SELECT MAX(ncf.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesCustomForms ncf
            WHERE ncf.node = :node AND ncf.field = :field')
                    ->setParameter('node', $node)
                    ->setParameter('field', $field);

        return (int) $query->getSingleScalarResult();
    }
}
