<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authentication;

use RZ\Roadiz\CoreBundle\Security\Authentication\Manager\LoginAttemptManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;

final class JwtAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    private LoginAttemptManager $loginAttemptManager;
    private AuthenticationFailureHandler $decorated;

    public function __construct(AuthenticationFailureHandler $decorated, LoginAttemptManager $loginAttemptManager)
    {
        $this->decorated = $decorated;
        $this->loginAttemptManager = $loginAttemptManager;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        try {
            $credentialData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $username = $credentialData['username'] ?? $credentialData['email'] ?? null;

            if (
                is_string($username) &&
                $exception instanceof BadCredentialsException
            ) {
                $this->loginAttemptManager->onFailedLoginAttempt($username);
            }
        } catch (\JsonException $exception) {
        }

        return $this->decorated->onAuthenticationFailure($request, $exception);
    }
}
