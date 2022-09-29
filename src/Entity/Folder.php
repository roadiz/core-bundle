<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as BaseFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimedPositioned;
use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use RZ\Roadiz\Core\AbstractEntities\LeafTrait;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Models\DocumentInterface;
use RZ\Roadiz\Core\Models\FolderInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Folders entity represent a directory on server with datetime and naming.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\FolderRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="folders", indexes={
 *     @ORM\Index(columns={"visible"}),
 *     @ORM\Index(columns={"locked"}),
 *     @ORM\Index(columns={"position"}),
 *     @ORM\Index(columns={"created_at"}),
 *     @ORM\Index(columns={"updated_at"}),
 *     @ORM\Index(columns={"parent_id", "position"}, name="folder_parent_position"),
 *     @ORM\Index(columns={"visible", "position"}, name="folder_visible_position"),
 *     @ORM\Index(columns={"parent_id", "visible"}, name="folder_parent_visible"),
 *     @ORM\Index(columns={"parent_id", "visible", "position"}, name="folder_parent_visible_position")
 * })
 * @ApiFilter(\ApiPlatform\Core\Serializer\Filter\PropertyFilter::class)
 * @ApiFilter(BaseFilter\OrderFilter::class, properties={
 *     "position",
 *     "createdAt",
 *     "updatedAt"
 * })
 * @UniqueEntity(fields={"folderName"})
 */
class Folder extends AbstractDateTimedPositioned implements FolderInterface
{
    use LeafTrait;

    /**
     * @ORM\ManyToOne(targetEntity="RZ\Roadiz\CoreBundle\Entity\Folder", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Folder|null
     * @Serializer\Groups({"folder_parent"})
     * @SymfonySerializer\Groups({"folder_parent"})
     * @SymfonySerializer\MaxDepth(1)
     * @ApiFilter(BaseFilter\SearchFilter::class, properties={
     *     "parent.id": "exact",
     *     "parent.folderName": "exact"
     * })
     */
    protected ?LeafInterface $parent = null;
    /**
     * @ORM\OneToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\Folder", mappedBy="parent", orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @var Collection<Folder>
     * @Serializer\Groups({"folder_children"})
     * @SymfonySerializer\Groups({"folder_children"})
     * @SymfonySerializer\MaxDepth(1)
     */
    protected Collection $children;
    /**
     * @ORM\ManyToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\Document", inversedBy="folders")
     * @ORM\JoinTable(name="documents_folders")
     * @var Collection<Document>
     * @Serializer\Groups({"folder"})
     * @SymfonySerializer\Ignore
     * @ApiFilter(BaseFilter\SearchFilter::class, properties={
     *     "documents.id": "exact",
     *     "documents.mimeType": "exact",
     *     "documents.filename": "exact",
     *     "documents.embedPlatform": "exact",
     *     "documents.folders": "exact",
     *     "documents.folders.folderName": "exact",
     * })
     */
    protected Collection $documents;
    /**
     * @ORM\Column(name="folder_name", type="string", unique=true, nullable=false)
     * @var string
     * @Serializer\Groups({"folder", "document_folders"})
     * @SymfonySerializer\Groups({"folder", "document_folders"})
     * @SymfonySerializer\SerializedName("slug")
     * @ApiFilter(BaseFilter\SearchFilter::class, strategy="partial")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max=250)
     */
    private string $folderName = '';
    /**
     * @var string
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    private string $dirtyFolderName = '';
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" = true})
     * @var bool
     * @Serializer\Groups({"folder", "document_folders"})
     * @SymfonySerializer\Groups({"folder", "document_folders"})
     * @ApiFilter(BaseFilter\BooleanFilter::class)
     */
    private bool $visible = true;
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"folder"})
     * @SymfonySerializer\Groups({"folder"})
     * @Serializer\Type("bool")
     * @ApiFilter(BaseFilter\BooleanFilter::class)
     */
    private bool $locked = false;
    /**
     * @var string
     * @ORM\Column(type="string", name="color", length=7, unique=false, nullable=false, options={"default" = "#000000"})
     * @Serializer\Groups({"folder", "folder_color"})
     * @SymfonySerializer\Groups({"folder", "folder_color"})
     * @Serializer\Type("string")
     */
    protected string $color = '#000000';
    /**
     * @ORM\OneToMany(targetEntity="FolderTranslation", mappedBy="folder", orphanRemoval=true)
     * @var Collection<FolderTranslation>
     * @Serializer\Groups({"folder", "document"})
     * @SymfonySerializer\Ignore()
     */
    private Collection $translatedFolders;

    /**
     * Create a new Folder.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->translatedFolders = new ArrayCollection();
        $this->initAbstractDateTimed();
    }

    /**
     * @param DocumentInterface $document
     * @return $this
     */
    public function addDocument(DocumentInterface $document)
    {
        if (!$this->getDocuments()->contains($document)) {
            $this->documents->add($document);
        }

        return $this;
    }

    /**
     * @return Collection<Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * @param DocumentInterface $document
     * @return $this
     */
    public function removeDocument(DocumentInterface $document)
    {
        if ($this->getDocuments()->contains($document)) {
            $this->documents->removeElement($document);
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @return boolean
     */
    public function getVisible(): bool
    {
        return $this->isVisible();
    }

    /**
     * @param bool $visible
     * @return Folder
     */
    public function setVisible($visible)
    {
        $this->visible = (bool) $visible;
        return $this;
    }

    /**
     * @return Collection<FolderTranslation>
     */
    public function getTranslatedFolders(): Collection
    {
        return $this->translatedFolders;
    }

    /**
     * @param Collection<FolderTranslation> $translatedFolders
     * @return Folder
     */
    public function setTranslatedFolders(Collection $translatedFolders)
    {
        $this->translatedFolders = $translatedFolders;
        return $this;
    }

    /**
     * @param TranslationInterface $translation
     * @return Collection<FolderTranslation>
     * @SymfonySerializer\Ignore
     */
    public function getTranslatedFoldersByTranslation(TranslationInterface $translation): Collection
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('translation', $translation));

        return $this->translatedFolders->matching($criteria);
    }

    /**
     * @return string
     */
    public function getFolderName()
    {
        return $this->folderName;
    }

    /**
     * @param string|null $folderName
     * @return Folder
     */
    public function setFolderName($folderName)
    {
        $this->dirtyFolderName = $folderName;
        $this->folderName = StringHandler::slugify($folderName ?? '');
        return $this;
    }

    /**
     * @return string|null
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"folder", "document_folders"})
     * @SymfonySerializer\Groups({"folder", "document_folders"})
     */
    public function getName(): ?string
    {
        return $this->getTranslatedFolders()->first() ?
            $this->getTranslatedFolders()->first()->getName() :
            $this->getFolderName();
    }

    /**
     * @return string
     */
    public function getDirtyFolderName()
    {
        return $this->dirtyFolderName;
    }

    /**
     * @param string $dirtyFolderName
     * @return Folder
     */
    public function setDirtyFolderName($dirtyFolderName)
    {
        $this->dirtyFolderName = $dirtyFolderName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     * @return Folder
     */
    public function setLocked(bool $locked): Folder
    {
        $this->locked = $locked;
        return $this;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     * @return Folder
     */
    public function setColor(string $color): Folder
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Get folder full path using folder names.
     *
     * @return string
     * @SymfonySerializer\Ignore
     */
    public function getFullPath(): string
    {
        $parents = $this->getParents();
        $path = [];

        /** @var Folder $parent */
        foreach ($parents as $parent) {
            $path[] = $parent->getFolderName();
        }

        $path[] = $this->getFolderName();

        return implode('/', $path);
    }
}
