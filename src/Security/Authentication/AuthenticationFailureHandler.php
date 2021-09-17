<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authentication;

use RZ\Roadiz\CoreBundle\Security\Authentication\Manager\LoginAttemptManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

/**
 * @deprecated Use Authenticators such as RozierAuthenticator
 */
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler implements LoginAttemptAwareInterface
{
    private LoginAttemptManager $loginAttemptManager;

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $username = $request->request->get('_username');
        $ipAddress = $request->getClientIp();
        if (null !== $this->logger) {
            $this->logger->error($exception->getMessage(), [
                'username' => $username,
                'ipAddress' => $ipAddress
            ]);
        }
        if (null !== $username &&
            is_string($username) &&
            null !== $this->getLoginAttemptManager() && $exception instanceof BadCredentialsException) {
            $this->getLoginAttemptManager()->onFailedLoginAttempt($username);
        }

        return parent::onAuthenticationFailure($request, $exception);
    }

    /**
     * @inheritDoc
     */
    public function getLoginAttemptManager(): LoginAttemptManager
    {
        return $this->loginAttemptManager;
    }

    /**
     * @inheritDoc
     */
    public function setLoginAttemptManager(LoginAttemptManager $loginAttemptManager)
    {
        $this->loginAttemptManager = $loginAttemptManager;
        return $this;
    }
}
