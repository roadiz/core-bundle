<?php

namespace RZ\Roadiz\CoreBundle\Security\Authentication;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Security\Authentication\Manager\LoginAttemptManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

abstract class RoadizAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'loginPage';

    private UrlGeneratorInterface $urlGenerator;
    private ManagerRegistry $managerRegistry;
    private LoginAttemptManager $loginAttemptManager;
    private LoggerInterface $logger;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ManagerRegistry $managerRegistry,
        LoginAttemptManager $loginAttemptManager,
        LoggerInterface $logger
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->managerRegistry = $managerRegistry;
        $this->loginAttemptManager = $loginAttemptManager;
        $this->logger = $logger;
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        $user = $token->getUser();
        if ($user instanceof UserInterface) {
            $this->loginAttemptManager->onSuccessLoginAttempt($user->getUsername());
        }
        if ($user instanceof User) {
            $user->setLastLogin(new \DateTime('now'));
            $manager = $this->managerRegistry->getManagerForClass(User::class);
            if (null !== $manager) {
                $manager->flush();
            }
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('adminHomePage'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $username = $request->request->get('_username') ??
            $request->request->get('username') ??
            $request->request->get('email');
        $ipAddress = $request->getClientIp();
        $this->logger->error($exception->getMessage(), [
            'username' => $username,
            'ipAddress' => $ipAddress
        ]);
        if (
            is_string($username) &&
            $exception instanceof BadCredentialsException
        ) {
            $this->loginAttemptManager->onFailedLoginAttempt($username);
        }

        return parent::onAuthenticationFailure($request, $exception);
    }


    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
