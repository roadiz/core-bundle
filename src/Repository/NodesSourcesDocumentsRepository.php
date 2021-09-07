<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments>
 */
class NodesSourcesDocumentsRepository extends EntityRepository
{
    /**
     * @param NodesSources $nodeSource
     * @param NodeTypeField $field
     * @return integer
     */
    public function getLatestPosition(NodesSources $nodeSource, NodeTypeField $field)
    {
        $query = $this->_em->createQuery('
            SELECT MAX(nsd.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments nsd
            WHERE nsd.nodeSource = :nodeSource AND nsd.field = :field')
                    ->setParameter('nodeSource', $nodeSource)
                    ->setParameter('field', $field);

        return (int) $query->getSingleScalarResult();
    }
}
