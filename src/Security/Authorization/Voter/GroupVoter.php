<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class GroupVoter extends RoleVoter
{
    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy, string $prefix = 'ROLE_')
    {
        $this->roleHierarchy = $roleHierarchy;
        parent::__construct($prefix);
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Group;
    }

    protected function extractRoles(TokenInterface $token)
    {
        return $this->roleHierarchy->getReachableRoleNames($token->getRoleNames());
    }

    /**
     * @inheritDoc
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        $roles = $this->extractRoles($token);
        $user = $token->getUser();

        foreach ($attributes as $attribute) {
            if (!($attribute instanceof Group)) {
                return VoterInterface::ACCESS_ABSTAIN;
            }

            $result = VoterInterface::ACCESS_GRANTED;

            /*
             * If super-admin, group is always granted
             */
            if (\in_array('ROLE_SUPER_ADMIN', $roles) || \in_array('ROLE_SUPERADMIN', $roles)) {
                return $result;
            }
            /*
             * If user is part of current tested group, grant it.
             */
            if (
                $user instanceof User &&
                $user->getGroups()->exists(function ($key, Group $group) use ($attribute) {
                    return $attribute->getId() === $group->getId();
                })
            ) {
                return $result;
            }

            /*
             * Test if user has all roles contained in Group to grant it access.
             */
            foreach ($this->extractGroupRoles($attribute) as $role) {
                if (!$this->isRoleContained($role, $roles)) {
                    $result = VoterInterface::ACCESS_DENIED;
                }
            }
        }

        return $result;
    }

    /**
     * @param Group $group
     *
     * @return string[]
     */
    protected function extractGroupRoles(Group $group)
    {
        return $this->roleHierarchy->getReachableRoleNames($group->getRoles());
    }

    /**
     * @param string $role
     * @param string[] $roles
     *
     * @return bool
     */
    protected function isRoleContained(string $role, array $roles)
    {
        foreach ($roles as $singleRole) {
            if ($role === $singleRole) {
                return true;
            }
        }
        return false;
    }
}