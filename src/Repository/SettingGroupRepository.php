<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<SettingGroup>
 */
final class SettingGroupRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, SettingGroup::class, $dispatcher);
    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function exists($name)
    {
        $query = $this->_em->createQuery('
            SELECT COUNT(s.id) FROM RZ\Roadiz\CoreBundle\Entity\SettingGroup s
            WHERE s.name = :name')
                        ->setParameter('name', $name);

        return (bool) $query->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function findAllNames()
    {
        $query = $this->_em->createQuery('SELECT s.name FROM RZ\Roadiz\CoreBundle\Entity\SettingGroup s');
        return array_map('current', $query->getScalarResult());
    }
}
