<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authentication;

use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Security\Authentication\Manager\LoginAttemptManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class JwtAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private ManagerRegistry $managerRegistry;
    private AuthenticationSuccessHandler $decorated;
    private LoginAttemptManager $loginAttemptManager;

    public function __construct(
        AuthenticationSuccessHandler $decorated,
        ManagerRegistry $managerRegistry,
        LoginAttemptManager $loginAttemptManager
    ) {
        $this->decorated = $decorated;
        $this->managerRegistry = $managerRegistry;
        $this->loginAttemptManager = $loginAttemptManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $response = $this->decorated->onAuthenticationSuccess($request, $token);
        $user = $token->getUser();

        if ($user instanceof User) {
            $user->setLastLogin(new \DateTime());
            $this->managerRegistry->getManager()->flush();
            $this->loginAttemptManager->onSuccessLoginAttempt($user->getUsername());
        }
        return $response;
    }
}
