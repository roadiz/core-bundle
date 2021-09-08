<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Font;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\Font>
 */
final class FontRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, Font::class, $dispatcher);
    }

    public function getLatestUpdateDate()
    {
        $query = $this->_em->createQuery('
            SELECT MAX(f.updatedAt) FROM RZ\Roadiz\CoreBundle\Entity\Font f');

        return $query->getSingleScalarResult();
    }
}
