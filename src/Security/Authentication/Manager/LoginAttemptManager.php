<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authentication\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\LoginAttempt;
use RZ\Roadiz\CoreBundle\Exception\TooManyLoginAttemptsException;
use RZ\Roadiz\CoreBundle\Repository\LoginAttemptRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated Use built-in Symfony Login throttling
 * @see https://symfony.com/doc/current/security.html#limiting-login-attempts
 */
class LoginAttemptManager
{
    protected int $ipAttemptGraceTime = 20 * 60;
    protected int $ipAttemptCount = 20;
    protected RequestStack $requestStack;
    protected ManagerRegistry $managerRegistry;
    protected LoggerInterface $logger;
    protected ?LoginAttemptRepository $loginAttemptRepository = null;

    /**
     * @param RequestStack $requestStack
     * @param ManagerRegistry $managerRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestStack $requestStack,
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger
    ) {
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $username
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkLoginAttempts(string $username): void
    {
        /*
         * Checks if there are more than 10 failed attempts
         * from same IP address in the last 20 minutes
         */
        if (
            $this->getLoginAttemptRepository()->isIpAddressBlocked(
                $this->requestStack->getMainRequest()->getClientIp(),
                $this->getIpAttemptGraceTime(),
                $this->getIpAttemptCount()
            )
        ) {
            throw new TooManyLoginAttemptsException(
                'Too many login attempts for current IP address, wait before trying again.',
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }
        if ($this->getLoginAttemptRepository()->isUsernameBlocked($username)) {
            throw new TooManyLoginAttemptsException(
                'Too many login attempts for this username, wait before trying again.',
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }
    }

    /**
     * @param string $username
     *
     * @return $this
     * @throws \Exception
     */
    public function onFailedLoginAttempt(string $username): LoginAttemptManager
    {
        $manager = $this->managerRegistry->getManagerForClass(LoginAttempt::class);
        if (null === $manager) {
            throw new \RuntimeException('No manager found for class ' . LoginAttempt::class);
        }
        $loginAttempt = $this->getLoginAttemptRepository()->findOrCreateOneByIpAddressAndUsername(
            $this->requestStack->getMainRequest()->getClientIp(),
            $username
        );

        $loginAttempt->addAttemptCount();
        $blocksUntil = new \DateTime();

        if ($loginAttempt->getAttemptCount() >= 9) {
            $blocksUntil->add(new \DateInterval('PT30M'));
            $loginAttempt->setBlocksLoginUntil($blocksUntil);
            $this->logger->info(sprintf(
                'Client has been blocked from login until %s',
                $blocksUntil->format('Y-m-d H:i:s')
            ));
        } elseif ($loginAttempt->getAttemptCount() >= 6) {
            $blocksUntil->add(new \DateInterval('PT15M'));
            $loginAttempt->setBlocksLoginUntil($blocksUntil);
            $this->logger->info(sprintf(
                'Client has been blocked from login until %s',
                $blocksUntil->format('Y-m-d H:i:s')
            ));
        } elseif ($loginAttempt->getAttemptCount() >= 3) {
            $blocksUntil->add(new \DateInterval('PT3M'));
            $loginAttempt->setBlocksLoginUntil($blocksUntil);
            $this->logger->info(sprintf(
                'Client has been blocked from login until %s',
                $blocksUntil->format('Y-m-d H:i:s')
            ));
        }
        $manager->flush();
        return $this;
    }

    /**
     * @return LoginAttemptRepository
     */
    public function getLoginAttemptRepository(): LoginAttemptRepository
    {
        if (null === $this->loginAttemptRepository) {
            $this->loginAttemptRepository = $this->managerRegistry->getRepository(LoginAttempt::class);
        }
        return $this->loginAttemptRepository;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function onSuccessLoginAttempt(string $username)
    {
        $this->getLoginAttemptRepository()->resetLoginAttempts(
            $this->requestStack->getMainRequest()->getClientIp(),
            $username
        );
        return $this;
    }

    /**
     * @return int
     */
    public function getIpAttemptGraceTime(): int
    {
        return $this->ipAttemptGraceTime;
    }

    /**
     * @return int
     */
    public function getIpAttemptCount(): int
    {
        return $this->ipAttemptCount;
    }
}
