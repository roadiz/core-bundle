<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

interface SearchResultsInterface extends \Iterator
{
    public function getResultCount(): int;
    public function getResultItems(): array;
    public function map(callable $callable): array;
}
