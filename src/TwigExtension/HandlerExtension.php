<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\EntityHandler\HandlerFactory;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class HandlerExtension extends AbstractExtension
{
    public function __construct(private readonly HandlerFactory $handlerFactory)
    {
    }

    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('handler', $this->getHandler(...)),
        ];
    }

    /**
     * @throws RuntimeError
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getHandler(mixed $mixed): ?AbstractHandler
    {
        if (null === $mixed) {
            return null;
        }

        if ($mixed instanceof PersistableInterface) {
            try {
                return $this->handlerFactory->getHandler($mixed);
            } catch (\InvalidArgumentException $exception) {
                throw new RuntimeError($exception->getMessage(), -1, null, $exception);
            }
        }

        throw new RuntimeError('Handler filter only supports AbstractEntity objects.');
    }
}
