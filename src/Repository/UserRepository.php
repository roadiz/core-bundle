<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<User>
 */
final class UserRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, User::class, $dispatcher);
    }

    /**
     * @param string $username
     *
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function usernameExists($username): bool
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select($qb->expr()->count('u.username'))
            ->andWhere($qb->expr()->eq('u.username', ':username'))
            ->setParameter('username', $username)
            ->setCacheable(true);

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $email
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function emailExists(string $email): bool
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select($qb->expr()->count('u.email'))
            ->andWhere($qb->expr()->eq('u.email', ':email'))
            ->setParameter('email', $email)
            ->setCacheable(true);

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }
}
