<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Loggable;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimedPositioned;
use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use RZ\Roadiz\Core\AbstractEntities\LeafTrait;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Model\AttributableInterface;
use RZ\Roadiz\CoreBundle\Model\AttributableTrait;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Node entities are the central feature of Roadiz,
 * it describes a document-like object which can be inherited
 * with *NodesSources* to create complex data structures.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\NodeRepository")
 * @ORM\Table(name="nodes", indexes={
 *     @ORM\Index(columns={"visible"}),
 *     @ORM\Index(columns={"status"}),
 *     @ORM\Index(columns={"locked"}),
 *     @ORM\Index(columns={"sterile"}),
 *     @ORM\Index(columns={"position"}),
 *     @ORM\Index(columns={"created_at"}),
 *     @ORM\Index(columns={"updated_at"}),
 *     @ORM\Index(columns={"hide_children"}),
 *     @ORM\Index(columns={"node_name", "status"}),
 *     @ORM\Index(columns={"visible", "status"}),
 *     @ORM\Index(columns={"visible", "status", "parent_node_id"}, name="node_visible_status_parent"),
 *     @ORM\Index(columns={"status", "parent_node_id"}, name="node_status_parent"),
 *     @ORM\Index(columns={"nodeType_id", "status", "parent_node_id"}, name="node_nodetype_status_parent"),
 *     @ORM\Index(columns={"nodeType_id", "status", "parent_node_id", "position"}, name="node_nodetype_status_parent_position"),
 *     @ORM\Index(columns={"visible", "parent_node_id"}, name="node_visible_parent"),
 *     @ORM\Index(columns={"visible", "parent_node_id", "position"}, name="node_visible_parent_position"),
 *     @ORM\Index(columns={"status", "visible", "parent_node_id", "position"}, name="node_status_visible_parent_position"),
 *     @ORM\Index(columns={"home"})
 * })
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable(logEntryClass="RZ\Roadiz\CoreBundle\Entity\UserLogEntry")
 * @UniqueEntity(fields={"nodeName"})
 */
#[ApiFilter(PropertyFilter::class)]
class Node extends AbstractDateTimedPositioned implements LeafInterface, AttributableInterface, Loggable
{
    use LeafTrait;
    use AttributableTrait;

    public const DRAFT = 10;
    public const PENDING = 20;
    public const PUBLISHED = 30;
    public const ARCHIVED = 40;
    public const DELETED = 50;

    /**
     * @var array
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    public static array $orderingFields = [
        'position' => 'position',
        'nodeName' => 'nodeName',
        'createdAt' => 'createdAt',
        'updatedAt' => 'updatedAt',
        'publishedAt' => 'ns.publishedAt',
    ];

    /**
     * @param int $status
     * @return string
     */
    public static function getStatusLabel($status): string
    {
        $nodeStatuses = [
            static::DRAFT => 'draft',
            static::PENDING => 'pending',
            static::PUBLISHED => 'published',
            static::ARCHIVED => 'archived',
            static::DELETED => 'deleted',
        ];

        if (isset($nodeStatuses[$status])) {
            return $nodeStatuses[$status];
        }

        throw new \InvalidArgumentException('Status does not exist.');
    }

    /**
     * @ORM\Column(type="string", name="node_name", unique=true)
     * @Serializer\Groups({"nodes_sources", "nodes_sources_base", "node", "log_sources"})
     * @SymfonySerializer\Groups({"nodes_sources", "nodes_sources_base", "node", "log_sources"})
     * @Serializer\Accessor(getter="getNodeName", setter="setNodeName")
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private string $nodeName = '';

    /**
     * @return string
     */
    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    /**
     * @param string $nodeName
     * @return $this
     */
    public function setNodeName(string $nodeName): Node
    {
        $this->nodeName = StringHandler::slugify($nodeName);
        return $this;
    }

    /**
     * @ORM\Column(type="boolean", name="dynamic_node_name", nullable=false, options={"default" = true})
     * @Gedmo\Versioned
     * @SymfonySerializer\Ignore()
     */
    private bool $dynamicNodeName = true;

    /**
     * Dynamic node name will be updated against default
     * translated nodeSource title at each save.
     *
     * Disable this parameter if you need to protect your nodeName
     * from title changes.
     *
     * @return bool
     */
    public function isDynamicNodeName(): bool
    {
        return $this->dynamicNodeName;
    }

    /**
     * @param bool $dynamicNodeName
     * @return $this
     */
    public function setDynamicNodeName(bool $dynamicNodeName): Node
    {
        $this->dynamicNodeName = (bool) $dynamicNodeName;
        return $this;
    }

    /**
     * @ORM\Column(type="boolean", name="home", nullable=false, options={"default" = false})
     * @Serializer\Groups({"nodes_sources_base", "nodes_sources", "node"})
     * @Serializer\Exclude(if="!object.getNodeType().isReachable()")
     * @SymfonySerializer\Ignore
     */
    private bool $home = false;

    /**
     * @return bool
     */
    public function isHome(): bool
    {
        return $this->home;
    }

    /**
     * @param bool $home
     * @return $this
     */
    public function setHome(bool $home): Node
    {
        $this->home = $home;
        return $this;
    }

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" = true})
     * @Gedmo\Versioned
     * @Serializer\Groups({"nodes_sources_base", "nodes_sources", "node"})
     * @SymfonySerializer\Groups({"nodes_sources_base", "nodes_sources", "node"})
     */
    private bool $visible = true;

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     * @return $this
     */
    public function setVisible(bool $visible): Node
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"node"})
     * @SymfonySerializer\Groups({"node"})
     * @SymfonySerializer\Ignore()
     * @internal You should use node Workflow to perform change on status.
     */
    private int $status = Node::DRAFT;

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return $this
     * @internal You should use node Workflow to perform change on status.
     */
    public function setStatus(int $status): Node
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, options={"default" = 0})
     * @Gedmo\Versioned
     * @Assert\GreaterThanOrEqual(value=0)
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore
     */
    private int $ttl = 0;

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     *
     * @return Node
     */
    public function setTtl(int $ttl): Node
    {
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return ($this->status === Node::PUBLISHED);
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return ($this->status === Node::PENDING);
    }

    /**
     * @return bool
     */
    public function isDraft(): bool
    {
        return ($this->status === Node::DRAFT);
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return ($this->status === Node::DELETED);
    }

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default" = false})
     * @Gedmo\Versioned
     * @Serializer\Groups({"node"})
     * @SymfonySerializer\Groups({"node"})
     * @SymfonySerializer\Ignore()
     */
    private bool $locked = false;

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     * @return $this
     */
    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;
        return $this;
    }

    /**
     * @var float|string
     * @ORM\Column(type="decimal", precision=2, scale=1)
     * @Gedmo\Versioned
     * @Serializer\Groups({"node"})
     * @SymfonySerializer\Groups({"node"})
     * @SymfonySerializer\Ignore()
     */
    private $priority = 0.8;

    /**
     * @return float|string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param float|string $priority
     * @return $this
     */
    public function setPriority($priority): Node
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="hide_children", nullable=false, options={"default" = false})
     * @Gedmo\Versioned
     * @Serializer\Groups({"node"})
     * @SymfonySerializer\Groups({"node"})
     * @SymfonySerializer\Ignore()
     */
    private bool $hideChildren = false;

    /**
     * @return bool
     */
    public function getHideChildren(): bool
    {
        return $this->hideChildren;
    }

    /**
     * @param bool $hideChildren
     * @return Node
     */
    public function setHideChildren(bool $hideChildren): Node
    {
        $this->hideChildren = $hideChildren;
        return $this;
    }


    /**
     * @return bool
     */
    public function isHidingChildren(): bool
    {
        return $this->hideChildren;
    }

    /**
     * @param bool $hideChildren
     *
     * @return $this
     */
    public function setHidingChildren(bool $hideChildren): Node
    {
        $this->hideChildren = $hideChildren;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return ($this->status === Node::ARCHIVED);
    }

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default" = false})
     * @Gedmo\Versioned
     * @Serializer\Groups({"node"})
     * @SymfonySerializer\Groups({"node"})
     * @SymfonySerializer\Ignore()
     */
    private bool $sterile = false;

    /**
     * @return bool
     */
    public function isSterile(): bool
    {
        return $this->sterile;
    }

    /**
     * @param bool $sterile
     * @return $this
     */
    public function setSterile(bool $sterile): Node
    {
        $this->sterile = $sterile;
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(type="string", name="children_order")
     * @Gedmo\Versioned
     * @Serializer\Groups({"node"})
     * @SymfonySerializer\Groups({"node"})
     * @SymfonySerializer\Ignore()
     */
    private string $childrenOrder = 'position';

    /**
     * @return string
     */
    public function getChildrenOrder(): string
    {
        return $this->childrenOrder;
    }

    /**
     * @param string $childrenOrder
     * @return $this
     */
    public function setChildrenOrder(string $childrenOrder): Node
    {
        $this->childrenOrder = $childrenOrder;
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(type="string", name="children_order_direction", length=4)
     * @Gedmo\Versioned
     * @Serializer\Groups({"node"})
     * @SymfonySerializer\Groups({"node"})
     * @SymfonySerializer\Ignore()
     */
    private string $childrenOrderDirection = 'ASC';

    /**
     * @return string
     */
    public function getChildrenOrderDirection(): string
    {
        return $this->childrenOrderDirection;
    }

    /**
     * @param string $childrenOrderDirection
     * @return $this
     */
    public function setChildrenOrderDirection(string $childrenOrderDirection): Node
    {
        $this->childrenOrderDirection = $childrenOrderDirection;
        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="NodeType")
     * @ORM\JoinColumn(name="nodeType_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"node"})
     * @SymfonySerializer\Groups({"node"})
     * @SymfonySerializer\Ignore()
     * @var NodeTypeInterface|null
     */
    private ?NodeTypeInterface $nodeType = null;

    /**
     * @return NodeTypeInterface|null
     */
    public function getNodeType(): ?NodeTypeInterface
    {
        return $this->nodeType;
    }

    /**
     * @param NodeTypeInterface|null $nodeType
     * @return $this
     */
    public function setNodeType(?NodeTypeInterface $nodeType = null): Node
    {
        $this->nodeType = $nodeType;
        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="RZ\Roadiz\CoreBundle\Entity\Node", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parent_node_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Node|null
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    private ?LeafInterface $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\Node", mappedBy="parent", orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @var Collection<Node>
     * @Serializer\Groups({"node_children"})
     * @SymfonySerializer\Groups({"node_children"})
     */
    private Collection $children;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="nodes")
     * @ORM\JoinTable(name="nodes_tags")
     * @var Collection<Tag>
     * @Serializer\Groups({"nodes_sources", "nodes_sources_base", "node"})
     * @SymfonySerializer\Groups({"nodes_sources", "nodes_sources_base", "node"})
     */
    private Collection $tags;

    /**
     * @return Collection<Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param Collection<Tag> $tags
     *
     * @return Node
     */
    public function setTags(Collection $tags): Node
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @param Tag $tag
     *
     * @return $this
     */
    public function removeTag(Tag $tag): Node
    {
        if ($this->getTags()->contains($tag)) {
            $this->getTags()->removeElement($tag);
        }

        return $this;
    }

    /**
     * @param Tag $tag
     *
     * @return $this
     */
    public function addTag(Tag $tag): Node
    {
        if (!$this->getTags()->contains($tag)) {
            $this->getTags()->add($tag);
        }

        return $this;
    }

    /**
     * @ORM\OneToMany(targetEntity="NodesCustomForms", mappedBy="node", fetch="EXTRA_LAZY")
     * @var Collection<NodesCustomForms>
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    private Collection $customForms;

    /**
     * @return Collection<NodesCustomForms>
     */
    public function getCustomForms()
    {
        return $this->customForms;
    }

    /**
     * @param Collection<NodesCustomForms> $customForms
     * @return Node
     */
    public function setCustomForms(Collection $customForms): Node
    {
        $this->customForms = $customForms;
        return $this;
    }

    /**
     * Used by generated nodes-sources.
     *
     * @param NodesCustomForms $nodesCustomForms
     * @return Node
     */
    public function addCustomForm(NodesCustomForms $nodesCustomForms): Node
    {
        if (!$this->customForms->contains($nodesCustomForms)) {
            $this->customForms->add($nodesCustomForms);
        }
        return $this;
    }

    /**
     * @ORM\ManyToMany(targetEntity="NodeType")
     * @ORM\JoinTable(name="stack_types", inverseJoinColumns={
     *     @ORM\JoinColumn(name="nodetype_id", onDelete="CASCADE")
     * })
     * @var Collection<NodeType>
     * @Serializer\Groups({"node"})
     * @SymfonySerializer\Groups({"node"})
     * @SymfonySerializer\Ignore()
     */
    private Collection $stackTypes;

    /**
     * @return Collection<NodeType>
     */
    public function getStackTypes(): Collection
    {
        return $this->stackTypes;
    }

    /**
     * @param NodeType $stackType
     *
     * @return $this
     */
    public function removeStackType(NodeType $stackType): Node
    {
        if ($this->getStackTypes()->contains($stackType)) {
            $this->getStackTypes()->removeElement($stackType);
        }

        return $this;
    }

    /**
     * @param NodeType $stackType
     *
     * @return $this
     */
    public function addStackType(NodeType $stackType): Node
    {
        if (!$this->getStackTypes()->contains($stackType)) {
            $this->getStackTypes()->add($stackType);
        }

        return $this;
    }

    /**
     * @ORM\OneToMany(targetEntity="NodesSources", mappedBy="node", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @Serializer\Groups({"node"})
     * @SymfonySerializer\Groups({"node"})
     * @SymfonySerializer\Ignore()
     * @var Collection<NodesSources>
     */
    private Collection $nodeSources;

    /**
     * @return Collection<NodesSources>
     */
    public function getNodeSources(): Collection
    {
        return $this->nodeSources;
    }

    /**
     * Get node-sources using a given translation.
     *
     * @param TranslationInterface $translation
     * @return Collection<NodesSources>
     * @SymfonySerializer\Ignore
     */
    public function getNodeSourcesByTranslation(TranslationInterface $translation): Collection
    {
        return $this->nodeSources->filter(function (NodesSources $nodeSource) use ($translation) {
            return $nodeSource->getTranslation()->getLocale() === $translation->getLocale();
        });
    }

    /**
     * @param NodesSources $ns
     *
     * @return $this
     */
    public function removeNodeSources(NodesSources $ns): Node
    {
        if ($this->getNodeSources()->contains($ns)) {
            $this->getNodeSources()->removeElement($ns);
        }

        return $this;
    }

    /**
     * @param NodesSources $ns
     *
     * @return $this
     */
    public function addNodeSources(NodesSources $ns): Node
    {
        if (!$this->getNodeSources()->contains($ns)) {
            $this->getNodeSources()->add($ns);
        }

        return $this;
    }

    /**
     * @ORM\OneToMany(
     *     targetEntity="NodesToNodes",
     *     mappedBy="nodeA",
     *     orphanRemoval=true,
     *     cascade={"persist"},
     *     fetch="LAZY"
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     * @var Collection<NodesToNodes>
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore
     */
    private Collection $bNodes;

    /**
     * Return nodes related to this (B nodes).
     *
     * @return Collection<NodesToNodes>
     */
    public function getBNodes(): Collection
    {
        return $this->bNodes;
    }

    /**
     * @param NodeTypeField $field
     *
     * @return Collection<NodesToNodes>
     * @SymfonySerializer\Ignore
     */
    public function getBNodesByField(NodeTypeField $field): Collection
    {
        $criteria = Criteria::create();
        $criteria->andWhere(Criteria::expr()->eq('field', $field));
        $criteria->orderBy(['position' => 'ASC']);
        return $this->getBNodes()->matching($criteria);
    }

    /**
     * @param ArrayCollection<NodesToNodes> $bNodes
     * @return Node
     */
    public function setBNodes(ArrayCollection $bNodes): Node
    {
        foreach ($this->bNodes as $bNode) {
            $bNode->setNodeA(null);
        }
        $this->bNodes->clear();
        foreach ($bNodes as $bNode) {
            if (!$this->hasBNode($bNode)) {
                $this->addBNode($bNode);
            }
        }
        return $this;
    }

    /**
     * @param NodesToNodes $bNode
     * @return $this
     */
    public function addBNode(NodesToNodes $bNode): Node
    {
        if (!$this->getBNodes()->contains($bNode)) {
            $this->getBNodes()->add($bNode);
            $bNode->setNodeA($this);
        }
        return $this;
    }

    public function hasBNode(NodesToNodes $bNode): bool
    {
        return $this->getBNodes()->exists(function ($key, NodesToNodes $element) use ($bNode) {
            return $bNode->getNodeB()->getId() !== null &&
                $element->getNodeB()->getId() === $bNode->getNodeB()->getId() &&
                $element->getField()->getId() === $bNode->getField()->getId();
        });
    }

    public function clearBNodesForField(NodeTypeField $nodeTypeField)
    {
        $toRemoveCollection = $this->getBNodes()->filter(function (NodesToNodes $element) use ($nodeTypeField) {
            return $element->getField()->getId() === $nodeTypeField->getId();
        });
        /** @var NodesToNodes $toRemove */
        foreach ($toRemoveCollection as $toRemove) {
            $this->getBNodes()->removeElement($toRemove);
            $toRemove->setNodeA(null);
        }
    }

    /**
     * @ORM\OneToMany(targetEntity="NodesToNodes", mappedBy="nodeB")
     * @var Collection<NodesToNodes>
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore
     */
    private Collection $aNodes;

    /**
     * Return nodes which own a relation with this (A nodes).
     *
     * @return Collection<NodesToNodes>
     */
    public function getANodes(): Collection
    {
        return $this->aNodes;
    }

    /**
     * @var Collection<AttributeValue>
     * @ORM\OneToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\AttributeValue", mappedBy="node", orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Serializer\Groups({"node_attributes"})
     * @SymfonySerializer\Groups({"node_attributes"})
     * @SymfonySerializer\MaxDepth(1)
     */
    private Collection $attributeValues;

    /**
     * Create a new empty Node according to given node-type.
     */
    public function __construct(NodeTypeInterface $nodeType = null)
    {
        $this->tags = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->nodeSources = new ArrayCollection();
        $this->stackTypes = new ArrayCollection();
        $this->customForms = new ArrayCollection();
        $this->aNodes = new ArrayCollection();
        $this->bNodes = new ArrayCollection();
        $this->attributeValues = new ArrayCollection();

        $this->setNodeType($nodeType);
        $this->initAbstractDateTimed();
    }

    /**
     * @return string
     * @SymfonySerializer\Ignore
     */
    public function getOneLineSummary(): string
    {
        return $this->getId() . " — " . $this->getNodeName() . " — " . $this->getNodeType()->getName() .
        " — Visible : " . ($this->isVisible() ? 'true' : 'false') . PHP_EOL;
    }

    /**
     * @return string
     * @SymfonySerializer\Ignore
     */
    public function getOneLineSourceSummary(): string
    {
        $text = "Source " . $this->getNodeSources()->first()->getId() . PHP_EOL;

        foreach ($this->getNodeType()->getFields() as $field) {
            $getterName = $field->getGetterName();
            $text .= '[' . $field->getLabel() . ']: ' . $this->getNodeSources()->first()->$getterName() . PHP_EOL;
        }

        return $text;
    }

    /**
     * After clone method.
     *
     * Clone current node and ist relations.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->home = false;
            $children = $this->getChildren();
            if ($children !== null) {
                $this->children = new ArrayCollection();
                foreach ($children as $child) {
                    $cloneChild = clone $child;
                    $this->addChild($cloneChild);
                }
            }
            $nodeSources = $this->getNodeSources();
            if ($nodeSources !== null) {
                $this->nodeSources = new ArrayCollection();
                /** @var NodesSources $nodeSource */
                foreach ($nodeSources as $nodeSource) {
                    $cloneNodeSource = clone $nodeSource;
                    $cloneNodeSource->setNode($this);
                }
            }
            $attributeValues = $this->getAttributeValues();
            if ($attributeValues !== null) {
                $this->attributeValues = new ArrayCollection();
                /** @var AttributeValue $attributeValue */
                foreach ($attributeValues as $attributeValue) {
                    $cloneAttributeValue = clone $attributeValue;
                    $cloneAttributeValue->setNode($this);
                    $this->addAttributeValue($cloneAttributeValue);
                }
            }
            // Get a random string after node-name.
            // This is for safety reasons
            // NodeDuplicator service will override it
            $namePrefix = $this->getNodeSources()->first()->getTitle() != "" ?
                $this->getNodeSources()->first()->getTitle() :
                $this->nodeName;
            $this->setNodeName($namePrefix . "-" . uniqid());
            $this->setCreatedAt(new \DateTime());
            $this->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '[Node]' . $this->getId() . " — " . $this->getNodeName() . " <" . $this->getNodeType()->getName() . '>';
    }
}
