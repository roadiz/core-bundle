<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Event\Role\PreCreatedRoleEvent;
use RZ\Roadiz\CoreBundle\Event\Role\PreDeletedRoleEvent;
use RZ\Roadiz\CoreBundle\Event\Role\PreUpdatedRoleEvent;
use RZ\Roadiz\CoreBundle\Event\Role\RoleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoleSubscriber implements EventSubscriberInterface
{
    protected ?LazyParameterBag $roles;
    private ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param LazyParameterBag|null $roles
     */
    public function __construct(ManagerRegistry $managerRegistry, ?LazyParameterBag $roles)
    {
        $this->roles = $roles;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PreCreatedRoleEvent::class => 'onRoleChanged',
            PreUpdatedRoleEvent::class => 'onRoleChanged',
            PreDeletedRoleEvent::class => 'onRoleChanged',
        ];
    }

    public function onRoleChanged(RoleEvent $event)
    {
        $manager = $this->managerRegistry->getManagerForClass(Role::class);
        // Clear result cache
        if (null !== $manager &&
            $manager instanceof EntityManagerInterface &&
            $manager->getConfiguration()->getResultCacheImpl() instanceof CacheProvider) {
            $manager->getConfiguration()->getResultCacheImpl()->deleteAll();
        }
        // Clear memory roles bag
        if (null !== $this->roles) {
            $this->roles->reset();
        }
    }
}
