<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Metadata\ApiFilter;
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
use RZ\Roadiz\CoreBundle\Repository\FolderRepository;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Folders entity represent a directory on server with datetime and naming.
 */
#[
    ORM\Entity(repositoryClass: FolderRepository::class),
    ORM\HasLifecycleCallbacks,
    ORM\Table(name: "folders"),
    ORM\Index(columns: ["visible"]),
    ORM\Index(columns: ["locked"]),
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["created_at"]),
    ORM\Index(columns: ["updated_at"]),
    ORM\Index(columns: ["parent_id", "position"], name: "folder_parent_position"),
    ORM\Index(columns: ["visible", "position"], name: "folder_visible_position"),
    ORM\Index(columns: ["parent_id", "visible"], name: "folder_parent_visible"),
    ORM\Index(columns: ["parent_id", "visible", "position"], name: "folder_parent_visible_position"),
    UniqueEntity(fields: ["folderName"]),
    ApiFilter(PropertyFilter::class),
    ApiFilter(BaseFilter\OrderFilter::class, properties: [
        "position",
        "createdAt",
        "updatedAt"
    ])
]
class Folder extends AbstractDateTimedPositioned implements FolderInterface
{
    use LeafTrait;

    /**
     * @var Folder|null
     * @Serializer\Groups({"folder_parent"})
     */
    #[ApiFilter(BaseFilter\SearchFilter::class, properties: [
        "parent.id" => "exact",
        "parent.folderName" => "exact"
    ])]
    #[ORM\ManyToOne(targetEntity: 'RZ\Roadiz\CoreBundle\Entity\Folder', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Groups(['folder_parent'])]
    #[SymfonySerializer\MaxDepth(1)]
    protected ?LeafInterface $parent = null;
    /**
     * @var Collection<Folder>
     * @Serializer\Groups({"folder_children"})
     */
    #[ORM\OneToMany(targetEntity: 'RZ\Roadiz\CoreBundle\Entity\Folder', mappedBy: 'parent', orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[SymfonySerializer\Groups(['folder_children'])]
    #[SymfonySerializer\MaxDepth(1)]
    protected Collection $children;
    /**
     * @var Collection<Document>
     * @Serializer\Groups({"folder"})
     */
    #[ORM\JoinTable(name: 'documents_folders')]
    #[ORM\ManyToMany(targetEntity: 'RZ\Roadiz\CoreBundle\Entity\Document', inversedBy: 'folders')]
    #[SymfonySerializer\Ignore]
    protected Collection $documents;
    /**
     * @var string
     * @Serializer\Groups({"folder", "document_folders"})
     */
    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: "partial")]
    #[ORM\Column(name: 'folder_name', type: 'string', unique: true, nullable: false)]
    #[SymfonySerializer\Groups(['folder', 'document_folders'])]
    #[SymfonySerializer\SerializedName('slug')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(max: 250)]
    private string $folderName = '';
    /**
     * @var string
     * @Serializer\Exclude()
     */
    #[SymfonySerializer\Ignore]
    private string $dirtyFolderName = '';
    /**
     * @var bool
     * @Serializer\Groups({"folder", "document_folders"})
     */
    #[ApiFilter(BaseFilter\BooleanFilter::class)]
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    #[SymfonySerializer\Groups(['folder', 'document_folders'])]
    private bool $visible = true;
    /**
     * @Serializer\Groups({"folder"})
     * @Serializer\Type("bool")
     */
    #[ApiFilter(BaseFilter\BooleanFilter::class)]
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Groups(['folder'])]
    private bool $locked = false;
    /**
     * @var string
     * @Serializer\Groups({"folder", "folder_color"})
     * @Serializer\Type("string")
     */
    #[ORM\Column(type: 'string', name: 'color', length: 7, unique: false, nullable: false, options: ['default' => '#000000'])]
    #[SymfonySerializer\Groups(['folder', 'folder_color'])]
    protected string $color = '#000000';
    /**
     * @var Collection<FolderTranslation>
     * @Serializer\Groups({"folder", "document"})
     */
    #[ORM\OneToMany(targetEntity: 'FolderTranslation', mappedBy: 'folder', orphanRemoval: true)]
    #[SymfonySerializer\Ignore]
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
     */
    #[SymfonySerializer\Ignore]
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
     */
    #[SymfonySerializer\Groups(['folder', 'document_folders'])]
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
     */
    #[SymfonySerializer\Ignore]
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
