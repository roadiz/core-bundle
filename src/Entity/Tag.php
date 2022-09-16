<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as BaseFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimedPositioned;
use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use RZ\Roadiz\Core\AbstractEntities\LeafTrait;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tags are hierarchical entities used
 * to qualify Nodes.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\TagRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="tags", indexes={
 *     @ORM\Index(columns={"visible"}),
 *     @ORM\Index(columns={"locked"}),
 *     @ORM\Index(columns={"position"}),
 *     @ORM\Index(columns={"created_at"}),
 *     @ORM\Index(columns={"updated_at"}),
 *     @ORM\Index(columns={"parent_tag_id", "position"}, name="tag_parent_position"),
 *     @ORM\Index(columns={"visible", "position"}, name="tag_visible_position"),
 *     @ORM\Index(columns={"parent_tag_id", "visible"}, name="tag_parent_visible"),
 *     @ORM\Index(columns={"parent_tag_id", "visible", "position"}, name="tag_parent_visible_position")
 * })
 * @ApiFilter(\ApiPlatform\Core\Serializer\Filter\PropertyFilter::class)
 * @ApiFilter(BaseFilter\OrderFilter::class, properties={
 *     "position",
 *     "createdAt",
 *     "updatedAt"
 * })
 * @UniqueEntity(fields={"tagName"})
 */
class Tag extends AbstractDateTimedPositioned implements LeafInterface
{
    use LeafTrait;

    /**
     * @var string
     * @ORM\Column(type="string", name="color", length=7, unique=false, nullable=false, options={"default" = "#000000"})
     * @Serializer\Groups({"tag", "tag_base", "color"})
     * @SymfonySerializer\Groups({"tag", "tag_base", "color"})
     * @Serializer\Type("string")
     */
    protected string $color = '#000000';
    /**
     * @ORM\ManyToOne(targetEntity="Tag", inversedBy="children", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="parent_tag_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Tag|null
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     * @ApiFilter(BaseFilter\SearchFilter::class, properties={
     *     "parent.id": "exact",
     *     "parent.tagName": "exact"
     * })
     */
    protected ?LeafInterface $parent = null;
    /**
     * @ORM\OneToMany(targetEntity="Tag", mappedBy="parent", orphanRemoval=true, cascade={"persist", "merge"})
     * @ORM\OrderBy({"position" = "ASC"})
     * @var Collection<Tag>
     * @Serializer\Groups({"tag"})
     * @SymfonySerializer\Groups({"tag"})
     * @Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\Tag>")
     * @Serializer\Accessor(setter="setChildren", getter="getChildren")
     */
    protected Collection $children;
    /**
     * @ORM\OneToMany(
     *     targetEntity="TagTranslation",
     *     mappedBy="tag",
     *     fetch="EAGER",
     *     orphanRemoval=true,
     *     cascade={"all"}
     * )
     * @var Collection<TagTranslation>
     * @Serializer\Groups({"translated_tag"})
     * @SymfonySerializer\Groups({"translated_tag"})
     * @Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\TagTranslation>")
     * @Serializer\Accessor(setter="setTranslatedTags", getter="getTranslatedTags")
     */
    protected Collection $translatedTags;
    /**
     * @var string
     * @ORM\Column(type="string", name="tag_name", unique=true)
     * @Serializer\Groups({"tag", "tag_base", "node", "nodes_sources"})
     * @SymfonySerializer\Groups({"tag", "tag_base", "node", "nodes_sources"})
     * @Serializer\Type("string")
     * @Serializer\Accessor(getter="getTagName", setter="setTagName")
     * @ApiFilter(BaseFilter\SearchFilter::class, strategy="partial")
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Length(max=250)
     */
    private string $tagName = '';
    /**
     * @var string
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    private string $dirtyTagName = '';
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" = true})
     * @Serializer\Groups({"tag", "tag_base", "node", "nodes_sources"})
     * @SymfonySerializer\Groups({"tag", "tag_base", "node", "nodes_sources"})
     * @Serializer\Type("bool")
     * @ApiFilter(BaseFilter\BooleanFilter::class)
     */
    private bool $visible = true;

    /**
     * @ORM\Column(type="string", name="children_order", options={"default" = "position"})
     * @Serializer\Groups({"tag"})
     * @SymfonySerializer\Groups({"tag"})
     * @Serializer\Type("string")
     */
    private string $childrenOrder = 'position';

    /**
     * @ORM\Column(type="string", name="children_order_direction", length=4, options={"default" = "ASC"})
     * @Serializer\Groups({"tag"})
     * @SymfonySerializer\Groups({"tag"})
     * @Serializer\Type("string")
     */
    private string $childrenOrderDirection = 'ASC';
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"tag"})
     * @SymfonySerializer\Groups({"tag"})
     * @Serializer\Type("bool")
     * @ApiFilter(BaseFilter\BooleanFilter::class)
     */
    private bool $locked = false;
    /**
     * @ORM\ManyToMany(targetEntity="Node", mappedBy="tags")
     * @ORM\JoinTable(name="nodes_tags")
     * @var Collection<Node>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     * @ApiFilter(BaseFilter\SearchFilter::class, properties={
     *     "nodes.id": "exact",
     *     "nodes.nodeName": "exact",
     *     "nodes.parent": "exact",
     *     "nodes.parent.nodeName": "exact",
     *     "nodes.tags": "exact",
     *     "nodes.tags.tagName": "exact",
     *     "nodes.nodeType": "exact",
     *     "nodes.nodeType.name": "exact",
     *     "nodes.parent.nodeType.name": "exact"
     * })
     */
    private Collection $nodes;

    /**
     * Create a new Tag.
     */
    public function __construct()
    {
        $this->nodes = new ArrayCollection();
        $this->translatedTags = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->initAbstractDateTimed();
    }

    /**
     * Gets the value of dirtyTagName.
     *
     * @return string
     */
    public function getDirtyTagName(): string
    {
        return $this->dirtyTagName;
    }

    /**
     * @param boolean $visible
     *
     * @return $this
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param boolean $locked
     *
     * @return $this
     */
    public function setLocked(bool $locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return Collection<Node>
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * Get tag full path using tag names.
     *
     * @return string
     */
    public function getFullPath(): string
    {
        $parents = $this->getParents();
        $path = [];

        /** @var Tag $parent */
        foreach ($parents as $parent) {
            $path[] = $parent->getTagName();
        }

        $path[] = $this->getTagName();

        return implode('/', $path);
    }

    /**
     * @return string
     */
    public function getTagName(): string
    {
        return $this->tagName;
    }

    /**
     * @param string $tagName
     *
     * @return $this
     */
    public function setTagName(string $tagName)
    {
        $this->dirtyTagName = $tagName;
        $this->tagName = StringHandler::slugify($tagName);

        return $this;
    }

    /**
     * @return Collection<TagTranslation>
     */
    public function getTranslatedTags(): Collection
    {
        return $this->translatedTags;
    }

    /**
     * @param Collection<TagTranslation> $translatedTags
     * @return Tag
     */
    public function setTranslatedTags(Collection $translatedTags): self
    {
        $this->translatedTags = $translatedTags;
        /** @var TagTranslation $translatedTag */
        foreach ($this->translatedTags as $translatedTag) {
            $translatedTag->setTag($this);
        }
        return $this;
    }

    /**
     * @param TranslationInterface $translation
     * @return Collection<TagTranslation>
     * @SymfonySerializer\Ignore
     */
    public function getTranslatedTagsByTranslation(TranslationInterface $translation): Collection
    {
        return $this->translatedTags->filter(function (TagTranslation $tagTranslation) use ($translation) {
            return $tagTranslation->getTranslation()->getLocale() === $translation->getLocale();
        });
    }

    /**
     * @return string
     */
    public function getOneLineSummary(): string
    {
        return $this->getId() . " — " . $this->getTagName() .
            " — Visible : " . ($this->isVisible() ? 'true' : 'false') . PHP_EOL;
    }

    /**
     * @return boolean
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Gets the value of color.
     *
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Sets the value of color.
     *
     * @param string|null $color the color
     *
     * @return self
     */
    public function setColor(?string $color)
    {
        $this->color = $color ?? '';

        return $this;
    }

    /**
     * Gets the value of childrenOrder.
     *
     * @return string
     */
    public function getChildrenOrder(): string
    {
        return $this->childrenOrder;
    }

    /**
     * Sets the value of childrenOrder.
     *
     * @param string $childrenOrder the children order
     *
     * @return self
     */
    public function setChildrenOrder(string $childrenOrder)
    {
        $this->childrenOrder = $childrenOrder;

        return $this;
    }

    /**
     * Gets the value of childrenOrderDirection.
     *
     * @return string
     */
    public function getChildrenOrderDirection(): string
    {
        return $this->childrenOrderDirection;
    }

    /**
     * Sets the value of childrenOrderDirection.
     *
     * @param string $childrenOrderDirection the children order direction
     *
     * @return self
     */
    public function setChildrenOrderDirection(string $childrenOrderDirection)
    {
        $this->childrenOrderDirection = $childrenOrderDirection;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '[' . ($this->getId() > 0 ? $this->getId() : 'NULL') . '] ' . $this->getTagName();
    }

    /**
     * @return string|null
     *
     * @Serializer\Groups({"tag", "tag_base", "node", "nodes_sources"})
     * @SymfonySerializer\Groups({"tag", "tag_base", "node", "nodes_sources"})
     * @Serializer\VirtualProperty
     * @Serializer\Type("string|null")
     */
    public function getName(): ?string
    {
        return $this->getTranslatedTags()->first() ?
            $this->getTranslatedTags()->first()->getName() :
            $this->getTagName();
    }

    /**
     * @return string|null
     *
     * @Serializer\Groups({"tag", "node", "nodes_sources"})
     * @SymfonySerializer\Groups({"tag", "node", "nodes_sources"})
     * @Serializer\VirtualProperty
     * @Serializer\Type("string|null")
     */
    public function getDescription(): ?string
    {
        return $this->getTranslatedTags()->first() ?
            $this->getTranslatedTags()->first()->getDescription() :
            '';
    }

    /**
     * @return array
     *
     * @Serializer\Groups({"tag", "node", "nodes_sources"})
     * @SymfonySerializer\Groups({"tag", "node", "nodes_sources"})
     * @Serializer\VirtualProperty
     * @Serializer\Type("array<RZ\Roadiz\CoreBundle\Entity\Document>")
     */
    public function getDocuments(): array
    {
        return $this->getTranslatedTags()->first() ?
            $this->getTranslatedTags()->first()->getDocuments() :
            [];
    }
}
