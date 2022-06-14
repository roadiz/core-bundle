<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authentication;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Query;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use RZ\Roadiz\OpenId\Authentication\JwtAccountToken;
use RZ\Roadiz\OpenId\Authentication\Provider\JwtRoleStrategy;
use RZ\Roadiz\OpenId\Discovery;
use RZ\Roadiz\OpenId\OpenIdJwtConfigurationFactory;
use RZ\Roadiz\OpenId\User\OpenIdAccount;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class OpenIdAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    private HttpUtils $httpUtils;
    private ?Discovery $discovery;
    private Client $client;
    private JwtRoleStrategy $roleStrategy;
    private OpenIdJwtConfigurationFactory $jwtConfigurationFactory;
    private UrlGeneratorInterface $urlGenerator;

    private string $returnPath;
    private string $defaultRoute;
    private ?string $oauthClientId;
    private ?string $oauthClientSecret;
    private string $usernameClaim;
    private string $targetPathParameter;
    private array $defaultRoles;

    public function __construct(
        HttpUtils $httpUtils,
        ?Discovery $discovery,
        JwtRoleStrategy $roleStrategy,
        OpenIdJwtConfigurationFactory $jwtConfigurationFactory,
        UrlGeneratorInterface $urlGenerator,
        string $returnPath,
        string $defaultRoute,
        ?string $oauthClientId,
        ?string $oauthClientSecret,
        string $usernameClaim = 'email',
        string $targetPathParameter = '_target_path',
        array $defaultRoles = []
    ) {
        $this->httpUtils = $httpUtils;
        $this->discovery = $discovery;
        $this->client = new Client([
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
        $this->roleStrategy = $roleStrategy;
        $this->returnPath = $returnPath;
        $this->oauthClientId = $oauthClientId;
        $this->oauthClientSecret = $oauthClientSecret;
        $this->usernameClaim = $usernameClaim;
        $this->targetPathParameter = $targetPathParameter;
        $this->defaultRoles = $defaultRoles;
        $this->defaultRoute = $defaultRoute;
        $this->urlGenerator = $urlGenerator;
        $this->jwtConfigurationFactory = $jwtConfigurationFactory;
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): ?bool
    {
        return null !== $this->discovery &&
            $this->httpUtils->checkRequestPath($request, $this->returnPath) &&
            $request->query->has('state') &&
            $request->query->has('scope') &&
            ($request->query->has('code') || $request->query->has('error'));
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        if (
            null !== $request->query->get('error') &&
            null !== $request->query->get('error_description')
        ) {
            throw new AuthenticationException((string) $request->query->get('error_description'));
        }

        if (null === $this->discovery) {
            throw new AuthenticationException('OpenId discovery service is unavailable, check your configuration.');
        }

        /*
         * Verify CSRF token passed to OAuth2 Service provider,
         * State is an url_encoded string containing the "token" and other
         * optional data
         */
        if (null === $request->query->get('state')) {
            throw new AuthenticationException('State is not valid');
        }
        $state = Query::parse((string) $request->query->get('state'));

        /*
         * Fetch _target_path parameter from OAuth2 state
         */
        if (
            isset($state[$this->targetPathParameter])
        ) {
            $request->query->set($this->targetPathParameter, $state[$this->targetPathParameter]);
        }

        try {
            $response = $this->client->post($this->discovery->get('token_endpoint'), [
                'form_params' => [
                    'code' => $request->query->get('code'),
                    'client_id' => $this->oauthClientId ?? '',
                    'client_secret' => $this->oauthClientSecret ?? '',
                    'redirect_uri' => $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo(),
                    'grant_type' => 'authorization_code'
                ]
            ]);
            $jsonResponse = \json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new AuthenticationException(
                'Cannot contact Identity provider to issue authorization_code.' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        if (empty($jsonResponse['id_token'])) {
            throw new AuthenticationException('JWT is missing from response.');
        }

        $jwt = $this->jwtConfigurationFactory
            ->create()
            ->parser()
            ->parse((string) $jsonResponse['id_token']);

        if (!($jwt instanceof Plain)) {
            throw new AuthenticationException(
                'JWT token must be instance of ' . Plain::class
            );
        }

        if (
            !$jwt->claims()->has($this->usernameClaim) ||
            empty($jwt->claims()->get($this->usernameClaim))
        ) {
            throw new AuthenticationException(
                'JWT does not contain “' . $this->usernameClaim . '” claim.'
            );
        }

        $username = (string) $jwt->claims()->get($this->usernameClaim);
        $passport = new Passport(
            new UserBadge($username, function () use ($jwt, $username) {
                $token = new JwtAccountToken(
                    new OpenIdAccount(
                        $username,
                        [],
                        $jwt
                    ),
                    $jwt,
                    $jwt->toString(),
                    OpenIdAuthenticator::class,
                    $this->defaultRoles
                );
                $roles = $this->defaultRoles;
                if ($this->roleStrategy->supports($token)) {
                    $roles = array_merge($roles, $this->roleStrategy->getRoles($token) ?? []);
                }

                return new OpenIdAccount(
                    $username,
                    array_unique($roles),
                    $jwt
                );
            }),
            new CustomCredentials(
                function (Plain $jwt) {
                    $configuration = $this->jwtConfigurationFactory->create();
                    $constraints = $configuration->validationConstraints();
                    try {
                        $configuration->validator()->assert($jwt, ...$constraints);
                    } catch (RequiredConstraintsViolated $e) {
                        throw new AuthenticationException(
                            $e->getMessage()
                        );
                    }
                    return true;
                },
                $jwt
            )
        );

        $passport->setAttribute('jwt', $jwt);
        $passport->setAttribute('token', !empty($jsonResponse['access_token']) ? $jsonResponse['access_token'] : $jwt->toString());

        return $passport;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate($this->defaultRoute));
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }
        $url = $this->urlGenerator->generate($this->defaultRoute);

        return new RedirectResponse($url);
    }
}
