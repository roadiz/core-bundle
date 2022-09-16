<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Contracts\NodeType\SerializableInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Form\Constraint as RoadizAssert;

/**
 * NodeTypeField entities are used to create NodeTypes with
 * custom data structure.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\NodeTypeFieldRepository")
 * @ORM\Table(name="node_type_fields", indexes={
 *         @ORM\Index(columns={"visible"}),
 *         @ORM\Index(columns={"indexed"}),
 *         @ORM\Index(columns={"position"}),
 *         @ORM\Index(columns={"group_name"}),
 *         @ORM\Index(columns={"group_name_canonical"}),
 *         @ORM\Index(columns={"type"}),
 *         @ORM\Index(columns={"universal"}),
 *         @ORM\Index(columns={"node_type_id", "position"}, name="ntf_type_position")
 *     },
 *     uniqueConstraints={@ORM\UniqueConstraint(columns={"name", "node_type_id"})}
 * )
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"name", "nodeType"})
 * @RoadizAssert\NodeTypeField
 */
class NodeTypeField extends AbstractField implements NodeTypeFieldInterface, SerializableInterface
{
    /**
     * If current field data should be the same over translations or not.
     *
     * @var bool
     * @ORM\Column(name="universal", type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("bool")
     */
    private bool $universal = false;

    /**
     * Exclude current field from full-text search engines.
     *
     * @var bool
     * @ORM\Column(name="exclude_from_search", type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("bool")
     */
    private bool $excludeFromSearch = false;

    /**
     * @var NodeTypeInterface|null
     * @ORM\ManyToOne(targetEntity="NodeType", inversedBy="fields")
     * @ORM\JoinColumn(name="node_type_id", onDelete="CASCADE")
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore
     */
    private ?NodeTypeInterface $nodeType = null;

    /**
     * @var string|null
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("string")
     * @ORM\Column(name="serialization_exclusion_expression", type="text", nullable=true)
     */
    private ?string $serializationExclusionExpression = null;

    /**
     * @var array|null
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("array<string>")
     * @ORM\Column(name="serialization_groups", type="json", nullable=true)
     */
    private ?array $serializationGroups = null;

    /**
     * @var int|null
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("int")
     * @ORM\Column(name="serialization_max_depth", type="integer", nullable=true)
     */
    private ?int $serializationMaxDepth = null;

    /**
     * @var bool
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("bool")
     * @ORM\Column(name="excluded_from_serialization", type="boolean", nullable=false, options={"default" = false})
     */
    private bool $excludedFromSerialization = false;

    /**
     * @return NodeTypeInterface|null
     */
    public function getNodeType(): ?NodeTypeInterface
    {
        return $this->nodeType;
    }

    /**
     * @param NodeTypeInterface|null $nodeType
     *
     * @return $this
     */
    public function setNodeType(?NodeTypeInterface $nodeType)
    {
        $this->nodeType = $nodeType;

        return $this;
    }

    /**
     * @return string
     * @Serializer\VirtualProperty()
     * @Serializer\Type("string")
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     */
    public function getNodeTypeName(): string
    {
        return $this->getNodeType() ? $this->getNodeType()->getName() : '';
    }

    /**
     * @var int|null
     * @ORM\Column(name="min_length", type="integer", nullable=true)
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("int")
     */
    private ?int $minLength = null;

    /**
     * @return int|null
     */
    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    /**
     * @param int|null $minLength
     *
     * @return $this
     */
    public function setMinLength(?int $minLength)
    {
        $this->minLength = $minLength;

        return $this;
    }

    /**
     * @var int|null
     * @ORM\Column(name="max_length", type="integer", nullable=true)
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("int")
     */
    private ?int $maxLength = null;

    /**
     * @return int|null
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    /**
     * @param int|null $maxLength
     *
     * @return $this
     */
    public function setMaxLength(?int $maxLength)
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("bool")
     */
    private bool $indexed = false;

    /**
     * @return boolean $isIndexed
     */
    public function isIndexed(): bool
    {
        return $this->indexed;
    }

    /**
     * @param bool $indexed
     * @return $this
     */
    public function setIndexed(bool $indexed)
    {
        $this->indexed = $indexed;
        return $this;
    }

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default" = true})
     * @Serializer\Groups({"node_type"})
     * @SymfonySerializer\Groups({"node_type"})
     * @Serializer\Type("bool")
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
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Tell if current field can be searched and indexed in a Search engine server.
     *
     * @return bool
     */
    public function isSearchable(): bool
    {
        return !$this->excludeFromSearch && in_array($this->getType(), static::$searchableTypes);
    }

    /**
     * @return string
     * @SymfonySerializer\Ignore
     */
    public function getOneLineSummary()
    {
        return $this->getId() . " — " . $this->getLabel() . ' [' . $this->getName() . ']' .
        ' - ' . $this->getTypeName() .
        ($this->isIndexed() ? ' - indexed' : '') .
        (!$this->isVisible() ? ' - hidden' : '') . PHP_EOL;
    }

    /**
     * @see Same as isUniversal
     * @return bool
     */
    public function getUniversal(): bool
    {
        return $this->universal;
    }

    /**
     * @return bool
     */
    public function isUniversal(): bool
    {
        return $this->universal;
    }

    /**
     * @param bool $universal
     * @return NodeTypeField
     */
    public function setUniversal(bool $universal)
    {
        $this->universal = $universal;
        return $this;
    }

    /**
     * @return bool
     */
    public function getExcludeFromSearch(): bool
    {
        return $this->excludeFromSearch;
    }

    /**
     * @return bool
     */
    public function isExcludeFromSearch(): bool
    {
        return $this->getExcludeFromSearch();
    }

    /**
     * @return bool
     */
    public function isExcludedFromSearch(): bool
    {
        return $this->getExcludeFromSearch();
    }

    /**
     * @param bool $excludeFromSearch
     *
     * @return NodeTypeField
     */
    public function setExcludeFromSearch(bool $excludeFromSearch)
    {
        $this->excludeFromSearch = $excludeFromSearch;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSerializationExclusionExpression(): ?string
    {
        return $this->serializationExclusionExpression;
    }

    /**
     * @param string|null $serializationExclusionExpression
     * @return NodeTypeField
     */
    public function setSerializationExclusionExpression(?string $serializationExclusionExpression): NodeTypeField
    {
        $this->serializationExclusionExpression = $serializationExclusionExpression;
        return $this;
    }

    /**
     * @return array
     */
    public function getSerializationGroups(): array
    {
        return array_filter($this->serializationGroups ?? []);
    }

    /**
     * @param array|null $serializationGroups
     * @return NodeTypeField
     */
    public function setSerializationGroups(?array $serializationGroups): NodeTypeField
    {
        $this->serializationGroups = $serializationGroups;
        if (null !== $this->serializationGroups) {
            $this->serializationGroups = array_filter($this->serializationGroups);
        }
        if (empty($this->serializationGroups)) {
            $this->serializationGroups = null;
        }
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSerializationMaxDepth(): ?int
    {
        return $this->serializationMaxDepth;
    }

    /**
     * @param int|null $serializationMaxDepth
     * @return NodeTypeField
     */
    public function setSerializationMaxDepth(?int $serializationMaxDepth): NodeTypeField
    {
        $this->serializationMaxDepth = $serializationMaxDepth;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExcludedFromSerialization(): bool
    {
        return $this->excludedFromSerialization;
    }

    /**
     * @param bool $excludedFromSerialization
     * @return NodeTypeField
     */
    public function setExcludedFromSerialization(bool $excludedFromSerialization): NodeTypeField
    {
        $this->excludedFromSerialization = $excludedFromSerialization;
        return $this;
    }
}
