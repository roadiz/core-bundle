<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class DiscriminatorFilter extends GeneratedEntityFilter
{
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
        if (preg_match($this->getGeneratedEntityNamespacePattern(), $resourceClass) > 0) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->isInstanceOf('o', $resourceClass));
        }
    }
}
