<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Document;

final class RawDocumentFilter extends AbstractFilter
{
    public function getDescription(string $resourceClass): array
    {
        return [];
    }

    /**
     * Passes a property through the filter.
     *
     * @param string $property
     * @param mixed $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param string|null $operationName
     * @throws \Exception
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if ($resourceClass !== Document::class) {
            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->expr()->eq('o.raw', ':raw'))
            ->setParameter(':raw', false);
    }
}
