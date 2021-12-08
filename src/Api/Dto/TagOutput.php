<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

final class TagOutput
{
    public string $slug = '';
    public ?string $name = null;
    public ?string $description = null;
    public ?string $color = null;
    public bool $visible = false;
}
