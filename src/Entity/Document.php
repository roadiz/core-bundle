<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Models\AbstractDocument;
use RZ\Roadiz\Core\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Core\Models\DisplayableInterface;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\Core\Models\FolderInterface;
use RZ\Roadiz\Core\Models\HasThumbnailInterface;
use RZ\Roadiz\Core\Models\SizeableInterface;
use RZ\Roadiz\Core\Models\TimeableInterface;
use RZ\Roadiz\Utils\StringHandler;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as BaseFilter;
use RZ\Roadiz\CoreBundle\Api\Filter as RoadizFilter;

/**
 * Documents entity represent a file on server with datetime and naming.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\DocumentRepository")
 * @ORM\Table(name="documents", indexes={
 *     @ORM\Index(columns={"created_at"}, name="document_created_at"),
 *     @ORM\Index(columns={"updated_at"}, name="document_updated_at"),
 *     @ORM\Index(columns={"raw"}),
 *     @ORM\Index(columns={"raw", "created_at"}, name="document_raw_created_at"),
 *     @ORM\Index(columns={"private"}),
 *     @ORM\Index(columns={"embedPlatform"}, name="document_embed_platform"),
 *     @ORM\Index(columns={"raw", "private"}),
 *     @ORM\Index(columns={"mime_type"})
 * })
 * @ApiFilter(\ApiPlatform\Core\Serializer\Filter\PropertyFilter::class)
 * @ApiFilter(RoadizFilter\RawDocumentFilter::class)
 * @ApiFilter(BaseFilter\OrderFilter::class, properties={
 *     "createdAt",
 *     "updatedAt",
 *     "filesize"
 * })
 */
class Document extends AbstractDocument implements AdvancedDocumentInterface, HasThumbnailInterface, SizeableInterface, TimeableInterface, DisplayableInterface
{
    /**
     * @ORM\OneToOne(targetEntity="Document", inversedBy="downscaledDocument", cascade={"all"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="raw_document", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"document"})
     * @SymfonySerializer\Groups({"document"})
     * @Serializer\Type("RZ\Roadiz\CoreBundle\Entity\Document")
     * @var Document|null
     */
    protected ?DocumentInterface $rawDocument = null;
    /**
     * @ORM\Column(type="boolean", name="raw", nullable=false, options={"default" = false})
     * @Serializer\Groups({"document"})
     * @SymfonySerializer\Groups({"document"})
     * @Serializer\Type("bool")
     */
    protected bool $raw = false;
    /**
     * @ORM\Column(type="string", name="embedId", unique=false, nullable=true)
     * @Serializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("string")
     */
    protected ?string $embedId = null;
    /**
     * @ORM\Column(type="string", name="embedPlatform", unique=false, nullable=true)
     * @Serializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("string")
     * @ApiFilter(BaseFilter\SearchFilter::class, strategy="exact")
     */
    protected ?string $embedPlatform = null;
    /**
     * @ORM\OneToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments", mappedBy="document")
     * @var Collection<NodesSourcesDocuments>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    protected Collection $nodesSourcesByFields;
    /**
     * @ORM\OneToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments", mappedBy="document")
     * @var Collection<TagTranslationDocuments>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    protected Collection $tagTranslations;
    /**
     * @ORM\OneToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\AttributeDocuments", mappedBy="document")
     * @var Collection<AttributeDocuments>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    protected Collection $attributeDocuments;
    /**
     * @ORM\ManyToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\CustomFormFieldAttribute", mappedBy="documents")
     * @var Collection<CustomFormFieldAttribute>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    protected Collection $customFormFieldAttributes;
    /**
     * @ORM\ManyToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\Folder", mappedBy="documents")
     * @ORM\JoinTable(name="documents_folders")
     * @Serializer\Groups({"document"})
     * @SymfonySerializer\Groups({"document"})
     * @Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\Folder>")
     * @ApiFilter(BaseFilter\SearchFilter::class, properties={
     *     "folders.id": "exact",
     *     "folders.folderName": "exact",
     * })
     * @ApiFilter(BaseFilter\BooleanFilter::class, properties={
     *     "folders.visible"
     * })
     * @var Collection<Folder>
     */
    protected Collection $folders;
    /**
     * @ORM\OneToMany(targetEntity="DocumentTranslation", mappedBy="document", orphanRemoval=true, fetch="EAGER")
     * @var Collection<DocumentTranslation>
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\DocumentTranslation>")
     */
    protected Collection $documentTranslations;
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("string")
     * @var string|null
     * @ApiFilter(BaseFilter\SearchFilter::class, strategy="partial")
     */
    private ?string $filename = null;
    /**
     * @ORM\Column(name="mime_type", type="string", nullable=true)
     * @Serializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("string")
     * @var string|null
     * @ApiFilter(BaseFilter\SearchFilter::class, strategy="exact")
     */
    private ?string $mimeType = null;
    /**
     * @ORM\OneToOne(targetEntity="Document", mappedBy="rawDocument")
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     * @var Document|null
     */
    private ?DocumentInterface $downscaledDocument = null;
    /**
     * @ORM\Column(type="string")
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("string")
     * @var string
     */
    private string $folder = '';
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("bool")
     * @var bool
     */
    private bool $private = false;
    /**
     * @var integer
     * @ORM\Column(name="imageWidth", type="integer", nullable=false, options={"default" = 0})
     * @Serializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("int")
     */
    private int $imageWidth = 0;
    /**
     * @var integer
     * @ORM\Column(name="imageHeight", type="integer", nullable=false, options={"default" = 0})
     * @Serializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("int")
     */
    private int $imageHeight = 0;
    /**
     * @var integer
     * @ORM\Column(type="integer", name="duration", nullable=false, options={"default" = 0})
     * @Serializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("int")
     */
    private int $mediaDuration = 0;
    /**
     * @var string|null
     * @ORM\Column(type="string", name="average_color", length=7, unique=false, nullable=true)
     * @Serializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("string")
     */
    private ?string $imageAverageColor = null;
    /**
     * @var int|null The filesize in bytes.
     * @ORM\Column(type="integer", nullable=true, unique=false)
     * @Serializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "document_display", "nodes_sources", "tag", "attribute"})
     * @Serializer\Type("int")
     */
    private ?int $filesize = null;

    /**
     * @var Collection<Document>
     * @ORM\OneToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\Document", mappedBy="original")
     * @Serializer\Groups({"document_thumbnails"})
     * @SymfonySerializer\Groups({"document_thumbnails"})
     * @Serializer\MaxDepth(2)
     * @SymfonySerializer\MaxDepth(2)
     * @Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\Document>")
     */
    private Collection $thumbnails;

    /**
     * @var Document|null
     * @ORM\ManyToOne(targetEntity="RZ\Roadiz\CoreBundle\Entity\Document", inversedBy="thumbnails")
     * @ORM\JoinColumn(name="original", nullable=true, onDelete="SET NULL")
     * @Serializer\Groups({"document_original"})
     * @SymfonySerializer\Groups({"document_original"})
     * @Serializer\MaxDepth(2)
     * @SymfonySerializer\MaxDepth(2)
     * @Serializer\Type("RZ\Roadiz\CoreBundle\Entity\Document")
     */
    private ?DocumentInterface $original = null;

    public function __construct()
    {
        parent::__construct();

        $this->folders = new ArrayCollection();
        $this->documentTranslations = new ArrayCollection();
        $this->nodesSourcesByFields = new ArrayCollection();
        $this->tagTranslations = new ArrayCollection();
        $this->attributeDocuments = new ArrayCollection();
        $this->customFormFieldAttributes = new ArrayCollection();
        $this->thumbnails = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename ?? '';
    }

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function setFilename(string $filename)
    {
        $this->filename = StringHandler::cleanForFilename($filename ?? '');

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     *
     * @return $this
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return string
     */
    public function getFolder(): string
    {
        return $this->folder;
    }

    /**
     * Set folder name.
     *
     * @param string $folder
     * @return $this
     */
    public function setFolder(string $folder)
    {
        $this->folder = $folder;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmbedId(): ?string
    {
        return $this->embedId;
    }

    /**
     * @param string|null $embedId
     * @return $this
     */
    public function setEmbedId(?string $embedId)
    {
        $this->embedId = $embedId;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmbedPlatform(): ?string
    {
        return $this->embedPlatform;
    }

    /**
     * @param string|null $embedPlatform
     * @return $this
     */
    public function setEmbedPlatform(?string $embedPlatform)
    {
        $this->embedPlatform = $embedPlatform;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param boolean $private
     * @return $this
     */
    public function setPrivate(bool $private)
    {
        $this->private = $private;
        if (null !== $raw = $this->getRawDocument()) {
            $raw->setPrivate($private);
        }

        return $this;
    }

    /**
     * @return Collection<NodesSourcesDocuments>
     * @SymfonySerializer\Ignore
     */
    public function getNodesSourcesByFields()
    {
        return $this->nodesSourcesByFields;
    }

    /**
     * @return Collection<TagTranslationDocuments>
     * @SymfonySerializer\Ignore
     */
    public function getTagTranslations()
    {
        return $this->tagTranslations;
    }

    /**
     * @return Collection<AttributeDocuments>
     * @SymfonySerializer\Ignore
     */
    public function getAttributeDocuments(): Collection
    {
        return $this->attributeDocuments;
    }

    /**
     * @param FolderInterface $folder
     * @return $this
     */
    public function addFolder(FolderInterface $folder)
    {
        if (!$this->getFolders()->contains($folder)) {
            $this->folders->add($folder);
            $folder->addDocument($this);
        }

        return $this;
    }

    /**
     * @return Collection<Folder>
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    /**
     * @param Collection<Folder> $folders
     * @return $this
     */
    public function setFolders(Collection $folders)
    {
        $this->folders = $folders;

        return $this;
    }

    /**
     * @param FolderInterface $folder
     * @return $this
     */
    public function removeFolder(FolderInterface $folder)
    {
        if ($this->getFolders()->contains($folder)) {
            $this->folders->removeElement($folder);
            $folder->removeDocument($this);
        }

        return $this;
    }

    /**
     * @param TranslationInterface $translation
     * @return Collection<DocumentTranslation>
     */
    public function getDocumentTranslationsByTranslation(TranslationInterface $translation)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('translation', $translation));

        return $this->documentTranslations->matching($criteria);
    }

    /**
     * @param DocumentTranslation $documentTranslation
     * @return $this
     */
    public function addDocumentTranslation(DocumentTranslation $documentTranslation)
    {
        if (!$this->getDocumentTranslations()->contains($documentTranslation)) {
            $this->documentTranslations->add($documentTranslation);
        }

        return $this;
    }

    /**
     * @return Collection<DocumentTranslation>
     */
    public function getDocumentTranslations()
    {
        return $this->documentTranslations;
    }

    /**
     * @return bool
     * @SymfonySerializer\Ignore
     */
    public function hasTranslations(): bool
    {
        return $this->getDocumentTranslations()->count() > 0;
    }

    /**
     * Gets the value of rawDocument.
     *
     * @return DocumentInterface|null
     * @SymfonySerializer\Ignore
     */
    public function getRawDocument(): ?DocumentInterface
    {
        return $this->rawDocument;
    }

    /**
     * Sets the value of rawDocument.
     *
     * @param DocumentInterface|null $rawDocument the raw document
     *
     * @return self
     */
    public function setRawDocument(DocumentInterface $rawDocument = null)
    {
        if (null === $rawDocument || $rawDocument instanceof Document) {
            $this->rawDocument = $rawDocument;
        }

        return $this;
    }

    /**
     * Is document a raw one.
     *
     * @return bool
     */
    public function isRaw(): bool
    {
        return $this->raw;
    }

    /**
     * Sets the value of raw.
     *
     * @param bool $raw the raw
     *
     * @return self
     */
    public function setRaw(bool $raw)
    {
        $this->raw = $raw;
        return $this;
    }

    /**
     * Gets the downscaledDocument.
     *
     * @return DocumentInterface|null
     */
    public function getDownscaledDocument(): ?DocumentInterface
    {
        return $this->downscaledDocument;
    }

    /**
     * @return int
     */
    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    /**
     * @param int $imageWidth
     *
     * @return Document
     */
    public function setImageWidth(int $imageWidth)
    {
        $this->imageWidth = $imageWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

    /**
     * @param int $imageHeight
     *
     * @return Document
     */
    public function setImageHeight(int $imageHeight)
    {
        $this->imageHeight = $imageHeight;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getImageRatio(): ?float
    {
        if ($this->getImageWidth() > 0 && $this->getImageHeight() > 0) {
            return $this->getImageWidth() / $this->getImageHeight();
        }
        return null;
    }

    /**
     * @return int
     */
    public function getMediaDuration(): int
    {
        return $this->mediaDuration;
    }

    /**
     * @param int $mediaDuration
     * @return Document
     */
    public function setMediaDuration(int $mediaDuration): Document
    {
        $this->mediaDuration = $mediaDuration;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageAverageColor(): ?string
    {
        return $this->imageAverageColor;
    }

    /**
     * @param string|null $imageAverageColor
     *
     * @return Document
     */
    public function setImageAverageColor(?string $imageAverageColor)
    {
        $this->imageAverageColor = $imageAverageColor;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFilesize(): ?int
    {
        return $this->filesize;
    }

    /**
     * @param int|null $filesize
     * @return Document
     */
    public function setFilesize(?int $filesize)
    {
        $this->filesize = $filesize;
        return $this;
    }

    public function getAlternativeText(): string
    {
        $documentTranslation = $this->getDocumentTranslations()->first();
        return $documentTranslation && !empty($documentTranslation->getName()) ?
            $documentTranslation->getName() :
            parent::getAlternativeText();
    }

    /**
     * Clone current document.
     * @SymfonySerializer\Ignore
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->rawDocument = null;
        }
    }

    /**
     * @return Collection
     */
    public function getThumbnails(): Collection
    {
        return $this->thumbnails;
    }

    /**
     * @param Collection $thumbnails
     *
     * @return Document
     */
    public function setThumbnails(Collection $thumbnails): Document
    {
        if ($this->thumbnails->count()) {
            /** @var HasThumbnailInterface $thumbnail */
            foreach ($this->thumbnails as $thumbnail) {
                $thumbnail->setOriginal(null);
            }
        }
        $this->thumbnails = $thumbnails->filter(function (HasThumbnailInterface $thumbnail) {
            return $thumbnail !== $this;
        });
        /** @var HasThumbnailInterface $thumbnail */
        foreach ($this->thumbnails as $thumbnail) {
            $thumbnail->setOriginal($this);
        }

        return $this;
    }

    /**
     * @return HasThumbnailInterface|null
     * @SymfonySerializer\Ignore
     */
    public function getOriginal(): ?HasThumbnailInterface
    {
        return $this->original;
    }

    /**
     * @param HasThumbnailInterface|null $original
     *
     * @return Document
     */
    public function setOriginal(?HasThumbnailInterface $original): Document
    {
        if (null === $original || ($original !== $this && $original instanceof Document)) {
            $this->original = $original;
        }

        return $this;
    }

    /**
     * @return bool
     * @Serializer\Groups({"document"})
     * @SymfonySerializer\Groups({"document"})
     * @Serializer\VirtualProperty()
     */
    public function isThumbnail(): bool
    {
        return $this->getOriginal() !== null;
    }

    /**
     * @return bool
     * @Serializer\Groups({"document"})
     * @SymfonySerializer\Groups({"document"})
     * @Serializer\VirtualProperty()
     */
    public function hasThumbnails(): bool
    {
        return $this->getThumbnails()->count() > 0;
    }

    /**
     * @return bool
     * @SymfonySerializer\Ignore
     */
    public function needsThumbnail(): bool
    {
        return !$this->isProcessable();
    }

    /**
     * @return string
     * @SymfonySerializer\Ignore
     */
    public function __toString()
    {
        if (!empty($this->getFilename())) {
            return $this->getFilename();
        }
        $translation = $this->getDocumentTranslations()->first();
        if (false !== $translation && !empty($translation->getName())) {
            return $translation->getName();
        }
        if (!empty($this->getEmbedPlatform())) {
            return $this->getEmbedPlatform() . ' (' . $this->getEmbedId() . ')';
        }
        return (string) $this->getId();
    }
}
