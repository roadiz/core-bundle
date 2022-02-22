<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview\User;

use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

final class PreviewUserProvider implements PreviewUserProviderInterface
{
    private PreviewResolverInterface $previewResolver;

    /**
     * @param PreviewResolverInterface $previewResolver
     */
    public function __construct(PreviewResolverInterface $previewResolver)
    {
        $this->previewResolver = $previewResolver;
    }

    public function createFromFullUser(UserInterface $user): UserInterface
    {
        if (!in_array($this->previewResolver->getRequiredRole(), $user->getRoles())) {
            throw new AccessDeniedException(
                'Cannot create a preview user proxy from a user that is not allowed to preview.'
            );
        }
        return new PreviewUser($user->getUserIdentifier(), [
            $this->previewResolver->getRequiredRole()
        ]);
    }
}
