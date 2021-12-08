<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class GeneratedEntityFilter extends AbstractFilter
{
    private string $generatedEntityNamespacePattern;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param RequestStack $requestStack
     * @param string $generatedEntityNamespacePattern
     * @param LoggerInterface|null $logger
     * @param array|null $properties
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        RequestStack $requestStack,
        string $generatedEntityNamespacePattern = '#^App\\\GeneratedEntity\\\NS(?:[a-zA-Z]+)$#',
        LoggerInterface $logger = null,
        array $properties = null
    ) {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties);

        $this->generatedEntityNamespacePattern = $generatedEntityNamespacePattern;
    }

    /**
     * @return string
     */
    public function getGeneratedEntityNamespacePattern(): string
    {
        return $this->generatedEntityNamespacePattern;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(string $resourceClass): array
    {
        return [];
    }
}
