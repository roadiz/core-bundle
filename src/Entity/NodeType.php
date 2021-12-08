<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Contracts\NodeType\SearchableInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Utils\StringHandler;

/**
 * NodeType describes each node structure family,
 * They are mandatory before creating any Node.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\NodeTypeRepository")
 * @ORM\Table(name="node_types", indexes={
 *     @ORM\Index(columns={"visible"}),
 *     @ORM\Index(columns={"publishable"}),
 *     @ORM\Index(columns={"hiding_nodes"}),
 *     @ORM\Index(columns={"hiding_non_reachable_nodes"}),
 *     @ORM\Index(columns={"reachable"}),
 *     @ORM\Index(columns={"searchable"}, name="nt_searchable")
 * })
 */
class NodeType extends AbstractEntity implements NodeTypeInterface, SearchableInterface
{
    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     * @Serializer\Groups({"node_type", "node"})
     * @SymfonySerializer\Groups({"node_type", "node"})
     * @Serializer\Type("string")
     */
    private string $name = '';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): NodeType
    {
        $this->name = StringHandler::classify($name ?? '');
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(name="display_name", type="string")
     * @Serializer\Groups({"node_type", "node"})
     * @SymfonySerializer\Groups({"node_type", "node"})
     * @Serializer\Type("string")
     */
    private string $displayName = '';

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getLabel(): string
    {
        return $this->getDisplayName();
    }

    /**
     * @param string|null $displayName
     *
     * @return $this
     */
    public function setDisplayName(?string $displayName): NodeType
    {
        $this->displayName = $displayName ?? '';
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("string")
     */
    private ?string $description = null;

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description = null)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default" = true})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("boolean")
     */
    private bool $visible = true;

    /**
     * @return boolean
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param boolean $visible
     * @return $this
     */
    public function setVisible(bool $visible): NodeType
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("boolean")
     */
    private bool $publishable = false;

    /**
     * @return bool
     */
    public function isPublishable(): bool
    {
        return $this->publishable;
    }

    /**
     * @param bool $publishable
     * @return NodeType
     */
    public function setPublishable(bool $publishable): NodeType
    {
        $this->publishable = $publishable;
        return $this;
    }

    /**
     * Define if this node-type produces nodes that will be
     * viewable from a Controller.
     *
     * Typically, if a node has a URL.
     *
     * @var bool
     * @ORM\Column(name="reachable", type="boolean", nullable=false, options={"default" = true})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("boolean")
     */
    private bool $reachable = true;

    /**
     * @return bool
     */
    public function getReachable(): bool
    {
        return $this->reachable;
    }

    /**
     * @return bool
     */
    public function isReachable(): bool
    {
        return $this->getReachable();
    }

    /**
     * @param bool $reachable
     * @return NodeType
     */
    public function setReachable(bool $reachable): NodeType
    {
        $this->reachable = $reachable;
        return $this;
    }

    /**
     * @var bool
     * @ORM\Column(name="hiding_nodes",type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("boolean")
     */
    private bool $hidingNodes = false;
    /**
     * @return boolean
     */
    public function isHidingNodes(): bool
    {
        return $this->hidingNodes;
    }
    /**
     * @param boolean $hidingNodes
     *
     * @return $this
     */
    public function setHidingNodes(bool $hidingNodes): NodeType
    {
        $this->hidingNodes = $hidingNodes;
        return $this;
    }

    /**
     * @var bool
     * @ORM\Column(name="hiding_non_reachable_nodes",type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("boolean")
     */
    private bool $hidingNonReachableNodes = false;

    /**
     * @return bool
     */
    public function isHidingNonReachableNodes(): bool
    {
        return $this->hidingNonReachableNodes;
    }

    /**
     * @param bool $hidingNonReachableNodes
     *
     * @return NodeType
     */
    public function setHidingNonReachableNodes(bool $hidingNonReachableNodes): NodeType
    {
        $this->hidingNonReachableNodes = $hidingNonReachableNodes;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(type="string", name="color", unique=false, nullable=true)
     * @Serializer\Groups({"node_type", "color"})
     * @SymfonySerializer\Groups({"node_type", "color"})
     * @Serializer\Type("string")
     */
    protected ?string $color = '#000000';

    /**
     * Gets the value of color.
     *
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * Sets the value of color.
     *
     * @param string|null $color
     *
     * @return $this
     */
    public function setColor(?string $color): NodeType
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @var ArrayCollection<NodeTypeField>
     * @ORM\OneToMany(targetEntity="NodeTypeField", mappedBy="nodeType", cascade={"persist", "merge"})
     * @ORM\OrderBy({"position" = "ASC"})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\NodeTypeField>")
     * @Serializer\Accessor(getter="getFields", setter="setFields")
     */
    private Collection $fields;

    /**
     * @return Collection<NodeTypeField>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    /**
     * @param ArrayCollection<NodeTypeField> $fields
     *
     * @return NodeType
     */
    public function setFields(ArrayCollection $fields): NodeType
    {
        $this->fields = $fields;
        foreach ($this->fields as $field) {
            $field->setNodeType($this);
        }

        return $this;
    }

    /**
     * @var int
     * @ORM\Column(type="integer", name="default_ttl", nullable=false, options={"default" = 0})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("int")
     */
    private int $defaultTtl = 0;

    /**
     * @return int
     */
    public function getDefaultTtl(): int
    {
        return $this->defaultTtl;
    }

    /**
     * @param int $defaultTtl
     *
     * @return NodeType
     */
    public function setDefaultTtl(int $defaultTtl): NodeType
    {
        $this->defaultTtl = $defaultTtl;

        return $this;
    }

    /**
     * Define if this node-type title will be indexed during its parent indexation.
     *
     * @var bool
     * @ORM\Column(name="searchable", type="boolean", nullable=false, options={"default" = true})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("boolean")
     */
    private bool $searchable = true;

    /**
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * @param bool $searchable
     * @return NodeType
     */
    public function setSearchable(bool $searchable): NodeType
    {
        $this->searchable = $searchable;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return NodeTypeField|null
     */
    public function getFieldByName(string $name): ?NodeTypeField
    {
        $fieldCriteria = Criteria::create();
        $fieldCriteria->andWhere(Criteria::expr()->eq('name', $name));
        $fieldCriteria->setMaxResults(1);
        $field = $this->getFields()->matching($fieldCriteria)->first();
        return $field ?: null;
    }
    /**
     * Get every node-type fields names in
     * a simple array.
     *
     * @return array
     * @SymfonySerializer\Ignore
     */
    public function getFieldsNames(): array
    {
        return array_map(function (NodeTypeField $field) {
            return $field->getName();
        }, $this->getFields()->toArray());
    }

    /**
     * @param NodeTypeField $field
     *
     * @return NodeType
     */
    public function addField(NodeTypeField $field): NodeType
    {
        if (!$this->getFields()->contains($field)) {
            $this->getFields()->add($field);
            $field->setNodeType($this);
        }

        return $this;
    }

    /**
     * @param NodeTypeField $field
     *
     * @return NodeType
     */
    public function removeField(NodeTypeField $field): NodeType
    {
        if ($this->getFields()->contains($field)) {
            $this->getFields()->removeElement($field);
        }

        return $this;
    }

    /**
     * Create a new NodeType.
     */
    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->name = 'Untitled';
        $this->displayName = 'Untitled node-type';
    }

    /**
     * Get node-source entity class name without its namespace.
     *
     * @return string
     * @SymfonySerializer\Ignore
     */
    public function getSourceEntityClassName(): string
    {
        return 'NS' . ucwords($this->getName());
    }

    /**
     * @template T of NodesSources
     * @return class-string<T>
     * @SymfonySerializer\Ignore
     */
    public function getSourceEntityFullQualifiedClassName(): string
    {
        return static::getGeneratedEntitiesNamespace() . '\\' . $this->getSourceEntityClassName();
    }

    /**
     * Get node-source entity database table name.
     *
     * @return string
     * @SymfonySerializer\Ignore
     */
    public function getSourceEntityTableName(): string
    {
        return 'ns_' . strtolower($this->getName());
    }

    /**
     * @return string
     * @SymfonySerializer\Ignore
     */
    public static function getGeneratedEntitiesNamespace(): string
    {
        return 'App\\GeneratedEntity';
    }

    /**
     * @return string
     * @SymfonySerializer\Ignore
     */
    public function __toString(): string
    {
        return '[#' . $this->getId() . '] ' . $this->getName() . ' (' . $this->getDisplayName() . ')';
    }

    /**
     * Get every searchable node-type fields as a Doctrine ArrayCollection.
     *
     * @return Collection
     * @SymfonySerializer\Ignore
     */
    public function getSearchableFields(): Collection
    {
        return $this->getFields()->filter(function (NodeTypeField $field) {
            return $field->isSearchable();
        });
    }
}
