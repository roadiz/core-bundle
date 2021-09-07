<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Translated representation of Tags.
 *
 * It stores their name and description.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\EntityRepository")
 * @ORM\Table(name="tags_translations", uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"tag_id", "translation_id"})
 * })
 * @Gedmo\Loggable(logEntryClass="RZ\Roadiz\CoreBundle\Entity\UserLogEntry")
 */
class TagTranslation extends AbstractEntity
{
    /**
     * @ORM\Column(type="string")
     * @Serializer\Groups({"tag", "node", "nodes_sources"})
     * @Serializer\Type("string")
     * @Gedmo\Versioned
     */
    protected $name = '';
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({"tag", "node", "nodes_sources"})
     * @Serializer\Type("string")
     * @Gedmo\Versioned
     */
    protected $description = null;
    /**
     * @ORM\ManyToOne(targetEntity="Tag", inversedBy="translatedTags")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Tag|null
     * @Serializer\Exclude()
     */
    protected $tag = null;
    /**
     * @ORM\ManyToOne(targetEntity="Translation", inversedBy="tagTranslations", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="translation_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Translation|null
     * @Serializer\Groups({"tag", "node", "nodes_sources"})
     * @Serializer\Type("RZ\Roadiz\CoreBundle\Entity\Translation")
     */
    protected $translation = null;
    /**
     * @ORM\OneToMany(
     *     targetEntity="RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments",
     *     mappedBy="tagTranslation",
     *     orphanRemoval=true,
     *     cascade={"persist", "merge"}
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     * @var Collection<TagTranslationDocuments>
     * @Serializer\Exclude
     */
    protected $tagTranslationDocuments;

    /**
     * Create a new TagTranslation with its origin Tag and Translation.
     *
     * @param Tag|null         $original
     * @param Translation|null $translation
     */
    public function __construct(Tag $original = null, Translation $translation = null)
    {
        $this->setTag($original);
        $this->setTranslation($translation);
        $this->tagTranslationDocuments = new ArrayCollection();

        if (null !== $original) {
            $this->name = $original->getDirtyTagName() != '' ? $original->getDirtyTagName() : $original->getTagName();
        }
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name): TagTranslation
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description): TagTranslation
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of tag.
     *
     * @return Tag
     */
    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    /**
     * Sets the value of tag.
     *
     * @param Tag $tag the tag
     *
     * @return self
     */
    public function setTag(?Tag $tag): TagTranslation
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Gets the value of translation.
     *
     * @return Translation
     */
    public function getTranslation(): ?Translation
    {
        return $this->translation;
    }

    /**
     * Sets the value of translation.
     *
     * @param Translation $translation the translation
     *
     * @return self
     */
    public function setTranslation(?Translation $translation): TagTranslation
    {
        $this->translation = $translation;

        return $this;
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
            $documents = $this->getDocuments();
            if ($documents !== null) {
                $this->tagTranslationDocuments = new ArrayCollection();
                /** @var TagTranslationDocuments $document */
                foreach ($documents as $document) {
                    $cloneDocument = clone $document;
                    $this->tagTranslationDocuments->add($cloneDocument);
                    $cloneDocument->setTagTranslation($this);
                }
            }
        }
    }

    /**
     * @return array
     *
     * @Serializer\Groups({"tag"})
     * @Serializer\VirtualProperty
     * @Serializer\Type("array<RZ\Roadiz\CoreBundle\Entity\Document>")
     */
    public function getDocuments(): array
    {
        return array_map(function (TagTranslationDocuments $tagTranslationDocument) {
            return $tagTranslationDocument->getDocument();
        }, $this->getTagTranslationDocuments()->toArray());
    }

    /**
     * @return Collection
     */
    public function getTagTranslationDocuments(): Collection
    {
        return $this->tagTranslationDocuments;
    }

    /**
     * @param ArrayCollection|null $tagTranslationDocuments
     * @return TagTranslation
     */
    public function setTagTranslationDocuments($tagTranslationDocuments): TagTranslation
    {
        $this->tagTranslationDocuments = $tagTranslationDocuments;
        return $this;
    }
}
