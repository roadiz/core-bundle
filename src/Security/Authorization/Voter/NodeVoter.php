<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

final class NodeVoter extends Voter
{
    public const CREATE = 'CREATE';
    public const DUPLICATE = 'DUPLICATE';
    public const CREATE_AT_ROOT = 'CREATE_AT_ROOT';
    public const READ = 'READ';
    public const READ_AT_ROOT = 'READ_AT_ROOT';
    public const EMPTY_TRASH = 'EMPTY_TRASH';
    public const READ_LOGS = 'READ_LOGS';
    public const EDIT_CONTENT = 'EDIT_CONTENT';
    public const EDIT_TAGS = 'EDIT_TAGS';
    public const EDIT_REALMS = 'EDIT_REALMS';
    public const EDIT_SETTING = 'EDIT_SETTING';
    public const EDIT_STATUS = 'EDIT_STATUS';
    public const EDIT_ATTRIBUTE = 'EDIT_ATTRIBUTE';
    public const DELETE = 'DELETE';

    public function __construct(
        private NodeChrootResolver $chrootResolver,
        private Security $security,
        private HandlerFactoryInterface $handlerFactory,
        private CacheItemPoolInterface $cache
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (
            \in_array($attribute, [
            self::CREATE_AT_ROOT,
            self::READ_AT_ROOT,
            self::EMPTY_TRASH,
            ])
        ) {
            return true;
        }

        if (
            !\in_array($attribute, [
            self::CREATE,
            self::DUPLICATE,
            self::READ,
            self::READ_LOGS,
            self::EDIT_CONTENT,
            self::EDIT_SETTING,
            self::EDIT_TAGS,
            self::EDIT_REALMS,
            self::EDIT_STATUS,
            self::EDIT_ATTRIBUTE,
            self::DELETE
            ])
        ) {
            return false;
        }

        if ($subject instanceof Node || $subject instanceof NodesSources) {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($subject instanceof NodesSources) {
            $subject = $subject->getNode();
        }

        return match ($attribute) {
            self::CREATE => $this->canCreate($subject, $user),
            self::DUPLICATE => $this->canDuplicate($subject, $user),
            self::CREATE_AT_ROOT => $this->canCreateAtRoot($user),
            self::READ => $this->canRead($subject, $user),
            self::READ_AT_ROOT => $this->canReadAtRoot($user),
            self::READ_LOGS => $this->canReadLogs($subject, $user),
            self::EDIT_CONTENT => $this->canEditContent($subject, $user),
            self::EDIT_SETTING => $this->canEditSetting($subject, $user),
            self::EDIT_STATUS => $this->canEditStatus($subject, $user),
            self::EDIT_TAGS => $this->canEditTags($subject, $user),
            self::EDIT_REALMS => $this->canEditRealms($subject, $user),
            self::EDIT_ATTRIBUTE => $this->canEditAttribute($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::EMPTY_TRASH => $this->canEmptyTrash($user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function isNodeInsideUserChroot(Node $node, Node $chroot, bool $includeChroot = false): bool
    {
        if (!$includeChroot && $chroot->getId() === $node->getId()) {
            return false;
        }

        /*
         * Test if node is inside user chroot using all Chroot node offspring ids
         * to be able to cache all results.
         */
        $cacheItem = $this->cache->getItem('node_offspring_ids_' . $chroot->getId());
        if (!$cacheItem->isHit()) {
            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($chroot);
            $offspringIds = $nodeHandler->getAllOffspringId();
            $cacheItem->set($offspringIds);
            $this->cache->save($cacheItem);
        } else {
            $offspringIds = $cacheItem->get();
        }

        return \in_array($node->getId(), $offspringIds, true);
    }

    private function isGrantedWithUserChroot(Node $node, User $user, array|string $roles, bool $includeChroot): bool
    {
        $chroot = $this->chrootResolver->getChroot($user);
        if (null === $chroot) {
            return $this->security->isGranted($roles);
        }

        return $this->security->isGranted($roles) &&
            $this->isNodeInsideUserChroot($node, $chroot, $includeChroot);
    }

    private function canCreateAtRoot(User $user): bool
    {
        $chroot = $this->chrootResolver->getChroot($user);
        return null === $chroot && $this->security->isGranted('ROLE_ACCESS_NODES');
    }

    private function canReadAtRoot(User $user): bool
    {
        $chroot = $this->chrootResolver->getChroot($user);
        return null === $chroot && $this->security->isGranted('ROLE_ACCESS_NODES');
    }

    private function canEmptyTrash(User $user): bool
    {
        $chroot = $this->chrootResolver->getChroot($user);
        return null === $chroot && $this->security->isGranted('ROLE_ACCESS_NODES_DELETE');
    }


    private function canCreate(Node $node, User $user): bool
    {
        /*
         * Creation is allowed only if node is inside user chroot,
         * user CAN create a chroot child.
         */
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES', true);
    }

    private function canRead(Node $node, User $user): bool
    {
        /*
         * Read is allowed only if node is inside user chroot,
         * user CAN read or list the chroot node children.
         */
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES', true);
    }

    private function canReadLogs(Node $node, User $user): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, ['ROLE_ACCESS_NODES', 'ROLE_ACCESS_LOGS'], false);
    }

    private function canEditContent(Node $node, User $user): bool
    {
        /*
         * Edition is allowed only if node is inside user chroot,
         * user cannot edit its chroot content.
         */
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES', false);
    }

    private function canEditTags(Node $node, User $user): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, ['ROLE_ACCESS_NODES', 'ROLE_ACCESS_TAGS'], false);
    }

    private function canEditRealms(Node $node, User $user): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, ['ROLE_ACCESS_NODES', 'ROLE_ACCESS_REALM_NODES'], false);
    }

    private function canDuplicate(Node $node, User $user): bool
    {
        /*
         * Duplication is allowed only if node is inside user chroot,
         * user cannot duplicate its chroot.
         */
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES', false);
    }

    private function canEditSetting(Node $node, User $user): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES_SETTING', false);
    }

    private function canEditStatus(Node $node, User $user): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES_STATUS', false);
    }

    private function canDelete(Node $node, User $user): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODES_DELETE', false);
    }

    private function canEditAttribute(Node $node, User $user): bool
    {
        return $this->isGrantedWithUserChroot($node, $user, 'ROLE_ACCESS_NODE_ATTRIBUTES', false);
    }
}
