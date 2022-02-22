<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Preview\User\PreviewUserProviderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class JwtExtension extends AbstractExtension
{
    private PreviewResolverInterface $previewResolver;
    private PreviewUserProviderInterface $previewUserProvider;
    private JWTTokenManagerInterface $tokenManager;
    private LoggerInterface $logger;


    public function __construct(
        JWTTokenManagerInterface $tokenManager,
        LoggerInterface $logger,
        PreviewResolverInterface $previewResolver,
        PreviewUserProviderInterface $previewUserProvider
    ) {
        $this->tokenManager = $tokenManager;
        $this->logger = $logger;
        $this->previewResolver = $previewResolver;
        $this->previewUserProvider = $previewUserProvider;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('createPreviewJwt', [$this, 'createPreviewJwt']),
        ];
    }

    public function createPreviewJwt(UserInterface $user): ?string
    {
        try {
            return $this->tokenManager->create($this->previewUserProvider->createFromFullUser($user));
        } catch (AccessDeniedException $exception) {
            $this->logger->warning($exception->getMessage());
            return null;
        } catch (JWTFailureException $exception) {
            $this->logger->warning($exception->getMessage());
            return null;
        }
    }
}
