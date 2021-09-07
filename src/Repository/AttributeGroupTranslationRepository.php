<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Model\AttributeGroupTranslationInterface;

final class AttributeGroupTranslationRepository extends EntityRepository
{
    public function findOneByNameAndLocale(string $name, string $locale): ?AttributeGroupTranslationInterface
    {
        $qb = $this->createQueryBuilder('agt');
        return $qb->innerJoin('agt.translation', 't')
            ->andWhere($qb->expr()->eq('t.locale', ':locale'))
            ->andWhere($qb->expr()->eq('agt.name', ':name'))
            ->setParameter('locale', $locale)
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
