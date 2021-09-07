<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\Font>
 */
class FontRepository extends EntityRepository
{
    public function getLatestUpdateDate()
    {
        $query = $this->_em->createQuery('
            SELECT MAX(f.updatedAt) FROM RZ\Roadiz\CoreBundle\Entity\Font f');

        return $query->getSingleScalarResult();
    }
}
