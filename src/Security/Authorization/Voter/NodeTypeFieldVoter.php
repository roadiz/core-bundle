<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class NodeTypeFieldVoter extends Voter
{
    public const VIEW = 'VIEW';

    public function __construct(
        private Security $security
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!\in_array($attribute, [self::VIEW])) {
            return false;
        }
        return $subject instanceof NodeTypeField;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(NodeTypeField $field, UserInterface $user): bool
    {
        if ($field->isNodes() && !$this->security->isGranted(NodeVoter::SEARCH)) {
            return false;
        }
        if ($field->isDocuments() && !$this->security->isGranted('ROLE_ACCESS_DOCUMENTS')) {
            return false;
        }
        if ($field->isUser() && !$this->security->isGranted('ROLE_ACCESS_USERS')) {
            return false;
        }
        if ($field->isCustomForms() && !$this->security->isGranted('ROLE_ACCESS_CUSTOMFORMS')) {
            return false;
        }

        return true;
    }
}
