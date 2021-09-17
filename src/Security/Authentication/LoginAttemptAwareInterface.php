<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authentication;

use RZ\Roadiz\CoreBundle\Security\Authentication\Manager\LoginAttemptManager;

interface LoginAttemptAwareInterface
{
    /**
     * @return LoginAttemptManager
     */
    public function getLoginAttemptManager(): LoginAttemptManager;

    /**
     * @param LoginAttemptManager $loginAttemptManager
     *
     * @return self
     */
    public function setLoginAttemptManager(LoginAttemptManager $loginAttemptManager);
}
