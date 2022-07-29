<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as BaseFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ObjectManager;
use Gedmo\Loggable\Loggable;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RuntimeException;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Filter as RoadizFilter;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * NodesSources store Node content according to a translation and a NodeType.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository")
 * @ORM\Table(name="nodes_sources", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"node_id", "translation_id"})
 * }, indexes={
 *     @ORM\Index(columns={"discr"}),
 *     @ORM\Index(columns={"title"}),
 *     @ORM\Index(columns={"published_at"}),
 *     @ORM\Index(columns={"node_id", "translation_id", "published_at"}, name="ns_node_translation_published"),
 *     @ORM\Index(columns={"node_id", "discr", "translation_id"}, name="ns_node_discr_translation"),
 *     @ORM\Index(columns={"node_id", "discr", "translation_id", "published_at"}, name="ns_node_discr_translation_published"),
 *     @ORM\Index(columns={"translation_id", "published_at"}, name="ns_translation_published"),
 *     @ORM\Index(columns={"discr", "translation_id"}, name="ns_discr_translation"),
 *     @ORM\Index(columns={"discr", "translation_id", "published_at"}, name="ns_discr_translation_published"),
 *     @ORM\Index(columns={"title", "published_at"}, name="ns_title_published"),
 *     @ORM\Index(columns={"title", "translation_id", "published_at"}, name="ns_title_translation_published")
 * })
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable(logEntryClass="RZ\Roadiz\CoreBundle\Entity\UserLogEntry")
 * @ApiFilter(\ApiPlatform\Core\Serializer\Filter\PropertyFilter::class)
 * @ApiFilter(RoadizFilter\DiscriminatorFilter::class)
 * @ApiFilter(RoadizFilter\LocaleFilter::class)
 * @ApiFilter(RoadizFilter\PublishableFilter::class)
 */
class NodesSources extends AbstractEntity implements Loggable
{
    /**
     * @var ObjectManager|null
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    protected ?ObjectManager $objectManager = null;

    /**
     * @inheritDoc
     * @Serializer\Exclude
     */
    public function injectObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @var Node|null
     * @ORM\ManyToOne(targetEntity="Node", inversedBy="nodeSources", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumn(name="node_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"nodes_sources", "nodes_sources_base", "log_sources"})
     * @SymfonySerializer\Groups({"nodes_sources", "nodes_sources_base", "log_sources"})
     * @ApiFilter(BaseFilter\SearchFilter::class, properties={
     *     "node.id": "exact",
     *     "node.nodeName": "exact",
     *     "node.parent": "exact",
     *     "node.parent.nodeName": "exact",
     *     "node.tags": "exact",
     *     "node.tags.tagName": "exact",
     *     "node.nodeType": "exact",
     *     "node.nodeType.name": "exact"
     * })
     * @ApiFilter(BaseFilter\OrderFilter::class, properties={
     *     "node.position",
     *     "node.createdAt",
     *     "node.updatedAt"
     * })
     * @ApiFilter(BaseFilter\DateFilter::class, properties={
     *     "node.createdAt",
     *     "node.updatedAt"
     * })
     * @ApiFilter(BaseFilter\BooleanFilter::class, properties={
     *     "node.visible",
     *     "node.home",
     *     "node.nodeType.reachable",
     *     "node.nodeType.publishable"
     * })
     * @ApiFilter(RoadizFilter\NotFilter::class, properties={
     *     "node.nodeType.name",
     *     "node.id",
     *     "node.tags.tagName"
     * })
     * Use IntersectionFilter after SearchFilter!
     * @ApiFilter(RoadizFilter\IntersectionFilter::class, properties={
     *     "node.tags",
     *     "node.tags.tagName"
     * })
     */
    private ?Node $node = null;

    /**
     * @return Node|null
     */
    public function getNode(): ?Node
    {
        return $this->node;
    }

    /**
     * @param Node|null $node
     *
     * @return $this
     */
    public function setNode(Node $node = null): NodesSources
    {
        $this->node = $node;
        if (null !== $node) {
            $node->addNodeSources($this);
        }

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        if (null !== $this->getNode()) {
            $this->getNode()->setUpdatedAt(new \DateTime("now"));
        }
    }

    /**
     * @var TranslationInterface|null
     * @ORM\ManyToOne(targetEntity="Translation", inversedBy="nodeSources")
     * @ORM\JoinColumn(name="translation_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"nodes_sources", "log_sources"})
     * @Serializer\Exclude(if="!object.isReachable()")
     * @SymfonySerializer\Ignore
     * @ApiFilter(BaseFilter\SearchFilter::class, properties={
     *     "translation.id": "exact",
     *     "translation.locale": "exact",
     * })
     */
    private ?TranslationInterface $translation = null;

    /**
     * @return TranslationInterface
     */
    public function getTranslation(): TranslationInterface
    {
        if (null === $this->translation) {
            throw new RuntimeException('Node source translation cannot be null.');
        }
        return $this->translation;
    }
    /**
     * @param TranslationInterface $translation
     *
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): NodesSources
    {
        $this->translation = $translation;
        return $this;
    }

    /**
     * @ORM\OneToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\UrlAlias", mappedBy="nodeSource", cascade={"all"})
     * @var Collection<UrlAlias>
     * @Serializer\Groups({"nodes_sources"})
     * @SymfonySerializer\Groups({"nodes_sources"})
     * @Serializer\Exclude(if="!object.isReachable()")
     * @SymfonySerializer\Ignore
     */
    private Collection $urlAliases;

    /**
     * @return Collection<UrlAlias>
     */
    public function getUrlAliases(): Collection
    {
        return $this->urlAliases;
    }

    /**
     * @param UrlAlias $urlAlias
     * @return $this
     */
    public function addUrlAlias(UrlAlias $urlAlias): NodesSources
    {
        if (!$this->urlAliases->contains($urlAlias)) {
            $this->urlAliases->add($urlAlias);
            $urlAlias->setNodeSource($this);
        }

        return $this;
    }

    /**
     * @ORM\OneToMany(
     *     targetEntity="RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments",
     *     mappedBy="nodeSource",
     *     orphanRemoval=true,
     *     cascade={"persist"},
     *     fetch="LAZY"
     * )
     * @var Collection<NodesSourcesDocuments>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    private Collection $documentsByFields;

    /**
     * @return Collection<NodesSourcesDocuments>
     */
    public function getDocumentsByFields(): Collection
    {
        return $this->documentsByFields;
    }

    /**
     * @param ArrayCollection<NodesSourcesDocuments> $documentsByFields
     *
     * @return NodesSources
     */
    public function setDocumentsByFields(ArrayCollection $documentsByFields): NodesSources
    {
        foreach ($this->documentsByFields as $documentsByField) {
            $documentsByField->setNodeSource(null);
        }
        $this->documentsByFields->clear();
        foreach ($documentsByFields as $documentsByField) {
            if (!$this->hasNodesSourcesDocuments($documentsByField)) {
                $this->addDocumentsByFields($documentsByField);
            }
        }

        return $this;
    }

    /**
     * Used by any NSClass to add directly new documents to source.
     *
     * @param NodesSourcesDocuments $nodesSourcesDocuments
     *
     * @return $this
     */
    public function addDocumentsByFields(NodesSourcesDocuments $nodesSourcesDocuments): NodesSources
    {
        if (!$this->getDocumentsByFields()->contains($nodesSourcesDocuments)) {
            $this->getDocumentsByFields()->add($nodesSourcesDocuments);
            $nodesSourcesDocuments->setNodeSource($this);
        }
        return $this;
    }

    /**
     * @param NodesSourcesDocuments $nodesSourcesDocuments
     * @return bool
     * @SymfonySerializer\Ignore
     */
    public function hasNodesSourcesDocuments(NodesSourcesDocuments $nodesSourcesDocuments): bool
    {
        return $this->getDocumentsByFields()->exists(
            function ($key, NodesSourcesDocuments $element) use ($nodesSourcesDocuments) {
                return $nodesSourcesDocuments->getDocument()->getId() !== null &&
                    $element->getDocument()->getId() === $nodesSourcesDocuments->getDocument()->getId() &&
                    $element->getField()->getId() === $nodesSourcesDocuments->getField()->getId();
            }
        );
    }

    public function clearDocumentsByFields(NodeTypeField $nodeTypeField)
    {
        $toRemoveCollection = $this->getDocumentsByFields()->filter(
            function (NodesSourcesDocuments $element) use ($nodeTypeField) {
                return $element->getField()->getId() === $nodeTypeField->getId();
            }
        );
        /** @var NodesSourcesDocuments $toRemove */
        foreach ($toRemoveCollection as $toRemove) {
            $this->getDocumentsByFields()->removeElement($toRemove);
            $toRemove->setNodeSource(null);
        }
    }

    /**
     * @param NodeTypeField $field
     *
     * @return Document[]
     */
    public function getDocumentsByFieldsWithField(NodeTypeField $field): array
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['position' => 'ASC']);
        return $this->getDocumentsByFields()
            ->matching($criteria)
            ->filter(function ($element) use ($field) {
                if ($element instanceof NodesSourcesDocuments) {
                    return $element->getField() === $field;
                }
                return false;
            })
            ->map(function (NodesSourcesDocuments $nodesSourcesDocuments) {
                return $nodesSourcesDocuments->getDocument();
            })
            ->toArray()
        ;
    }

    /**
     * @param string $fieldName
     * @return Document[]
     */
    public function getDocumentsByFieldsWithName(string $fieldName): array
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['position' => 'ASC']);
        return $this->getDocumentsByFields()
            ->matching($criteria)
            ->filter(function ($element) use ($fieldName) {
                if ($element instanceof NodesSourcesDocuments) {
                    return $element->getField()->getName() === $fieldName;
                }
                return false;
            })
            ->map(function (NodesSourcesDocuments $nodesSourcesDocuments) {
                return $nodesSourcesDocuments->getDocument();
            })
            ->toArray()
        ;
    }

    /**
     * @ORM\OneToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\Log", mappedBy="nodeSource")
     * @ORM\OrderBy({"datetime" = "DESC"})
     * @var Collection<Log>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    protected Collection $logs;

    /**
     * @ORM\OneToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\Redirection", mappedBy="redirectNodeSource")
     * @var Collection<Redirection>
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    protected Collection $redirections;

    /**
     * Logs related to this node-source.
     *
     * @return Collection<Log>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    /**
     * @param Collection $logs
     * @return $this
     */
    public function setLogs(Collection $logs): NodesSources
    {
        $this->logs = $logs;

        return $this;
    }

    /**
     * @return Collection<Redirection>
     */
    public function getRedirections(): Collection
    {
        return $this->redirections;
    }

    /**
     * @param Collection<Redirection> $redirections
     * @return NodesSources
     */
    public function setRedirections(Collection $redirections): NodesSources
    {
        $this->redirections = $redirections;
        return $this;
    }

    /**
     * @ORM\Column(type="string", name="title", unique=false, nullable=true)
     * @Serializer\Groups({"nodes_sources", "nodes_sources_base", "log_sources"})
     * @SymfonySerializer\Groups({"nodes_sources", "nodes_sources_base", "log_sources"})
     * @Gedmo\Versioned
     * @ApiFilter(BaseFilter\SearchFilter::class, strategy="partial")
     */
    protected ?string $title = null;

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): NodesSources
    {
        $this->title = null !== $title ? trim($title) : null;
        return $this;
    }

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", name="published_at", unique=false, nullable=true)
     * @Serializer\Groups({"nodes_sources", "nodes_sources_base"})
     * @SymfonySerializer\Groups({"nodes_sources", "nodes_sources_base"})
     * @Gedmo\Versioned
     * @Serializer\Exclude(if="!object.isPublishable()")
     * @ApiFilter(BaseFilter\DateFilter::class)
     * @ApiFilter(BaseFilter\OrderFilter::class)
     * @ApiFilter(RoadizFilter\ArchiveFilter::class)
     */
    protected ?\DateTime $publishedAt = null;

    /**
     * @return \DateTime|null
     */
    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param \DateTime|null $publishedAt
     * @return NodesSources
     */
    public function setPublishedAt(\DateTime $publishedAt = null): NodesSources
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * @ORM\Column(type="string", name="meta_title", unique=false)
     * @Serializer\Groups({"nodes_sources"})
     * @SymfonySerializer\Groups({"nodes_sources"})
     * @Gedmo\Versioned
     * @Serializer\Exclude(if="!object.isReachable()")
     * @ApiFilter(BaseFilter\SearchFilter::class, strategy="partial")
     */
    protected string $metaTitle = '';

    /**
     * @return string
     */
    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    /**
     * @param string|null $metaTitle
     *
     * @return $this
     */
    public function setMetaTitle(?string $metaTitle): NodesSources
    {
        $this->metaTitle = null !== $metaTitle ? trim($metaTitle) : '';

        return $this;
    }
    /**
     * @ORM\Column(type="text", name="meta_keywords")
     * @Serializer\Groups({"nodes_sources"})
     * @SymfonySerializer\Groups({"nodes_sources"})
     * @Serializer\Exclude(if="!object.isReachable()")
     * @Gedmo\Versioned
     */
    protected string $metaKeywords = '';

    /**
     * @return string
     */
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    /**
     * @param string|null $metaKeywords
     *
     * @return $this
     */
    public function setMetaKeywords(?string $metaKeywords): NodesSources
    {
        $this->metaKeywords = null !== $metaKeywords ? trim($metaKeywords) : '';

        return $this;
    }
    /**
     * @ORM\Column(type="text", name="meta_description")
     * @Serializer\Groups({"nodes_sources"})
     * @SymfonySerializer\Groups({"nodes_sources"})
     * @Serializer\Exclude(if="!object.isReachable()")
     * @ApiFilter(BaseFilter\SearchFilter::class, strategy="partial")
     * @Gedmo\Versioned
     */
    protected string $metaDescription = '';

    /**
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    /**
     * @param string|null $metaDescription
     *
     * @return $this
     */
    public function setMetaDescription(?string $metaDescription): NodesSources
    {
        $this->metaDescription = null !== $metaDescription ? trim($metaDescription) : '';

        return $this;
    }

    /**
     * Create a new NodeSource with its Node and Translation.
     *
     * @param Node $node
     * @param TranslationInterface $translation
     */
    public function __construct(Node $node, TranslationInterface $translation)
    {
        $this->setNode($node);
        $this->translation = $translation;
        $this->urlAliases = new ArrayCollection();
        $this->documentsByFields = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->redirections = new ArrayCollection();
    }

    /**
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("slug")
     * @SymfonySerializer\SerializedName("slug")
     * @Serializer\Groups({"nodes_sources", "nodes_sources_base"})
     * @SymfonySerializer\Groups({"nodes_sources", "nodes_sources_base"})
     */
    public function getIdentifier(): string
    {
        $urlAlias = $this->getUrlAliases()->first();
        if (false !== $urlAlias && $urlAlias->getAlias() !== '') {
            return $urlAlias->getAlias();
        }

        return $this->getNode()->getNodeName();
    }

    /**
     * Get parent node’ source based on the same translation.
     *
     * @return NodesSources|null
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    public function getParent(): ?NodesSources
    {
        /** @var Node|null $parent */
        $parent = $this->getNode()->getParent();
        if (null !== $parent) {
            /** @var NodesSources|false $nodeSources */
            $nodeSources = $parent->getNodeSourcesByTranslation($this->translation)->first();
            return $nodeSources ?: null;
        } else {
            return null;
        }
    }

    /**
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"nodes_sources", "nodes_sources_default"})
     * @SymfonySerializer\Groups({"nodes_sources", "nodes_sources_default"})
     * @Serializer\SerializedName("@type")
     * @SymfonySerializer\SerializedName("@type")
     */
    public function getNodeTypeName(): string
    {
        return 'NodesSources';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '#' . $this->getId() .
        ' <' . $this->getTitle() . '>[' . $this->getTranslation()->getLocale() .
        '], type="' . $this->getNodeTypeName() . '"';
    }

    /**
     * Overridden in NS classes.
     *
     * @return bool
     */
    public function isPublishable(): bool
    {
        return $this->getNode()->getNodeType()->isPublishable();
    }

    /**
     * Overridden in NS classes.
     *
     * @return bool
     */
    public function isReachable(): bool
    {
        return $this->getNode()->getNodeType()->isReachable();
    }

    /**
     * After clone method.
     *
     * Be careful not to persist nor flush current entity after
     * calling clone as it empties its relations.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $documentsByFields = $this->getDocumentsByFields();
            $this->documentsByFields = new ArrayCollection();
            foreach ($documentsByFields as $documentsByField) {
                $cloneDocumentsByField = clone $documentsByField;
                $this->documentsByFields->add($cloneDocumentsByField);
                $cloneDocumentsByField->setNodeSource($this);
            }
            // Clear url-aliases before cloning.
            $this->urlAliases->clear();
            // Clear logs before cloning.
            $this->logs->clear();
        }
    }
}
