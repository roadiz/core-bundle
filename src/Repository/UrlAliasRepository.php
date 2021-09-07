<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

/**
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\UrlAlias>
 */
class UrlAliasRepository extends EntityRepository
{
    /**
     * Get all url aliases linked to given node.
     *
     * @param integer $nodeId
     *
     * @return array
     */
    public function findAllFromNode($nodeId)
    {
        $query = $this->_em->createQuery('
            SELECT ua FROM RZ\Roadiz\CoreBundle\Entity\UrlAlias ua
            INNER JOIN ua.nodeSource ns
            INNER JOIN ns.node n
            WHERE n.id = :nodeId')
                        ->setParameter('nodeId', (int) $nodeId);

        return $query->getResult();
    }

    /**
     * @param string $alias
     *
     * @return boolean
     */
    public function exists($alias)
    {
        $query = $this->_em->createQuery('
            SELECT COUNT(ua.alias) FROM RZ\Roadiz\CoreBundle\Entity\UrlAlias ua
            WHERE ua.alias = :alias')
                        ->setParameter('alias', $alias);

        return (boolean) $query->getSingleScalarResult();
    }
}
