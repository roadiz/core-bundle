<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Loggable;

use Gedmo\Loggable\LogEntryInterface;
use Gedmo\Loggable\LoggableListener;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;
use Symfony\Component\Security\Core\User\UserInterface;

class UserLoggableListener extends LoggableListener
{
    protected ?UserInterface $user = null;

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * @param UserInterface|null $user
     *
     * @return UserLoggableListener
     */
    public function setUser(?UserInterface $user): UserLoggableListener
    {
        $this->user = $user;
        if (null !== $user) {
            $this->setUsername($user->getUsername());
        }

        return $this;
    }

    /**
     * Handle any custom LogEntry functionality that needs to be performed
     * before persisting it
     *
     * @param LogEntryInterface $logEntry The LogEntry being persisted
     * @param object $object   The object being Logged
     *
     * @return void
     */
    protected function prePersistLogEntry($logEntry, $object): void
    {
        parent::prePersistLogEntry($logEntry, $object);

        $user = $this->getUser();
        if ($logEntry instanceof UserLogEntry && $user instanceof User) {
            $logEntry->setUser($user);
        }
    }
}
