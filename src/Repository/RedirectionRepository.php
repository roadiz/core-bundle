<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class RedirectionRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($registry, Redirection::class, $dispatcher);
    }
}
