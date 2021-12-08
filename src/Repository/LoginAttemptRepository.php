<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\LoginAttempt;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<LoginAttempt>
 */
final class LoginAttemptRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, LoginAttempt::class, $dispatcher);
    }

    /**
     * @param string $username
     *
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isUsernameBlocked(string $username): bool
    {
        $qb = $this->createQueryBuilder('la');
        return $qb->select('COUNT(la)')
            ->andWhere($qb->expr()->gte('la.blocksLoginUntil', ':now'))
            ->andWhere($qb->expr()->eq('la.username', ':username'))
            ->getQuery()
            ->setParameters([
                'now' =>  new \DateTime('now'),
                'username' => $username,
            ])
            ->getSingleScalarResult() > 0
        ;
    }

    /**
     * Checks if an IP address tries more than 10 usernames
     * in the last 5 minutes.
     *
     * @param string $ipAddress
     * @param int    $seconds
     * @param int    $count
     *
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIpAddressBlocked(string $ipAddress, int $seconds = 1200, int $count = 10): bool
    {
        $qb = $this->createQueryBuilder('la');
        $query = $qb->select('SUM(la.attemptCount)')
            ->andWhere($qb->expr()->gte('la.date', ':now'))
            ->andWhere($qb->expr()->eq('la.ipAddress', ':ipAddress'))
            ->getQuery()
            ->setParameters([
                'now' =>  (new \DateTime())->sub(new \DateInterval('PT' . $seconds . 'S')),
                'ipAddress' => $ipAddress,
            ])
        ;
        return $query->getSingleScalarResult() > $count ? true : false;
    }

    /**
     * @param string $ipAddress
     * @param string $username
     *
     * @return LoginAttempt
     * @throws \Doctrine\ORM\ORMException
     */
    public function findOrCreateOneByIpAddressAndUsername(string $ipAddress, string $username): LoginAttempt
    {
        /** @var LoginAttempt|null $loginAttempt */
        $loginAttempt = $this->findOneBy([
            'ipAddress' => $ipAddress,
            'username' => $username,
        ]);
        if (null === $loginAttempt) {
            $loginAttempt = new LoginAttempt($ipAddress, $username);
            $this->_em->persist($loginAttempt);
        }

        return $loginAttempt;
    }

    /**
     * @param string $ipAddress
     * @param string $username
     */
    public function resetLoginAttempts(string $ipAddress, string $username): void
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(LoginAttempt::class, 'la')
            ->andWhere($qb->expr()->eq('la.ipAddress', ':ipAddress'))
            ->andWhere($qb->expr()->eq('la.username', ':username'))
            ->getQuery()
            ->execute([
                'username' => $username,
                'ipAddress' => $ipAddress,
            ])
        ;
    }

    /**
     * @param string $ipAddress
     */
    public function purgeLoginAttempts(string $ipAddress): void
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(LoginAttempt::class, 'la')
            ->andWhere($qb->expr()->eq('la.ipAddress', ':ipAddress'))
            ->getQuery()
            ->execute([
                'ipAddress' => $ipAddress,
            ])
        ;
    }

    public function cleanLoginAttempts(): void
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(LoginAttempt::class, 'la')
            ->andWhere($qb->expr()->lte('la.blocksLoginUntil', ':date'))
            ->getQuery()
            ->execute([
                'date' =>  (new \DateTime())->sub(new \DateInterval('P1D')),
            ])
        ;
    }
}
