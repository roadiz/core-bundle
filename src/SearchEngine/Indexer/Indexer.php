<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Indexer;

interface Indexer
{
    public function reindexAll(): void;
    public function index($id): void;
    public function delete($id): void;
    public function emptySolr(?string $documentType = null): void;
    public function optimizeSolr(): void;
}
