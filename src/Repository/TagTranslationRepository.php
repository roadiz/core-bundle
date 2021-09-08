<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class TagTranslationRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($registry, TagTranslation::class, $dispatcher);
    }
}
