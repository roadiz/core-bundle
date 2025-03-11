<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview;

interface PreviewResolverInterface
{
    public function isPreview(): bool;

    public function getRequiredRole(): string;
}
