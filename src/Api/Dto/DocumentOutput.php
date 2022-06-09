<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Component\Serializer\Annotation\Groups;

final class DocumentOutput
{
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $relativePath = null;
    /**
     * @var string
     * @Groups({"document", "document_display"})
     */
    public string $type = '';
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $mimeType = null;
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
    public ?string $embedId = null;
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $embedPlatform = null;
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $imageAverageColor = null;
    /**
     * @var int|null
     * @Groups({"document", "document_display"})
     */
    public ?int $imageWidth = null;
    /**
     * @var int|null
     * @Groups({"document", "document_display"})
     */
    public ?int $imageHeight = null;
    /**
     * @var int|null
     * @Groups({"document", "document_display"})
     */
    public ?int $mediaDuration = null;
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $copyright = null;
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $externalUrl = null;
    /**
     * @var bool
     * @Groups({"document", "document_display"})
     */
    public bool $processable = false;
    /**
     * @var Document|null
     * @Groups({"document", "document_display"})
     */
    public ?Document $thumbnail = null;
    /**
     * @var array<Folder>
     * @Groups({"document", "document_display", "folder"})
     */
    public array $folders = [];
    /**
     * @var string|null
     * @Groups({"document", "document_display"})
     */
    public ?string $alt = null;
}
