<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use RZ\Roadiz\Core\Models\DocumentInterface;

final class DocumentOutput
{
    public string $relativePath = '';
    public string $type = '';
    public ?string $name = null;
    public ?string $description = null;
    public ?string $copyright = null;
    public bool $processable = false;
    public ?DocumentInterface $thumbnail = null;
    public ?string $alt = null;
}
