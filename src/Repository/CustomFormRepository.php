<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\CustomForm>
 */
class CustomFormRepository extends EntityRepository
{
    /**
     * @param Node          $node
     * @param NodeTypeField $field
     *
     * @return array
     */
    public function findByNodeAndField($node, NodeTypeField $field)
    {
        $query = $this->_em->createQuery('
            SELECT cf FROM RZ\Roadiz\CoreBundle\Entity\CustomForm cf
            INNER JOIN cf.nodes ncf
            WHERE ncf.field = :field AND ncf.node = :node
            ORDER BY ncf.position ASC')
                        ->setParameter('field', $field)
                        ->setParameter('node', $node);

        return $query->getResult();
    }

    /**
     * @deprecated Use findByNodeAndField instead because **filtering on field name is not safe**.
     * @param Node $node
     * @param string $fieldName
     *
     * @return array
     */
    public function findByNodeAndFieldName($node, $fieldName)
    {
        trigger_error(
            'Method ' . __METHOD__ . ' is deprecated. Use findByNodeAndField instead because **filtering on field name is not safe**.',
            E_USER_DEPRECATED
        );
        $query = $this->_em->createQuery('
            SELECT cf FROM RZ\Roadiz\CoreBundle\Entity\CustomForm cf
            INNER JOIN cf.nodes ncf
            INNER JOIN ncf.field f
            WHERE f.name = :name AND ncf.node = :node
            ORDER BY ncf.position ASC')
                        ->setParameter('name', (string) $fieldName)
                        ->setParameter('node', $node);
        return $query->getResult();
    }
}
