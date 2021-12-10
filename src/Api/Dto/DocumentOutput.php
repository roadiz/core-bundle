<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use RZ\Roadiz\Core\Models\DocumentInterface;
use Symfony\Component\Serializer\Annotation\Groups;

final class DocumentOutput
{
    /**
     * @var string
     * @Groups({"document", "document_display"})
     */
    public string $relativePath = '';
    /**
     * @var string
     * @Groups({"document", "document_display"})
     */
    public string $type = '';
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $name = null;
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $description = null;
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $copyright = null;
    /**
     * @var bool
     * @Groups({"document", "document_display"})
     */
    public bool $processable = false;
    /**
     * @var DocumentOutput|null
     * @Groups({"document", "document_display"})
     */
    public ?DocumentOutput $thumbnail = null;
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $alt = null;
}
