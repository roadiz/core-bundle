<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Repository\TagTranslationRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Translated representation of Tags.
 *
 * It stores their name and description.
 */
#[
    ORM\Entity(repositoryClass: TagTranslationRepository::class),
    ORM\Table(name: "tags_translations"),
    ORM\UniqueConstraint(columns: ["tag_id", "translation_id"]),
    Gedmo\Loggable(logEntryClass: UserLogEntry::class),
    UniqueEntity(fields: ["tag", "translation"])
]
class TagTranslation extends AbstractEntity
{
    #[ORM\Column(type: 'string')]
    #[SymfonySerializer\Groups(['tag', 'node', 'nodes_sources'])]
    #[Serializer\Groups(['tag', 'node', 'nodes_sources'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    #[Gedmo\Versioned]
    protected string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    #[SymfonySerializer\Groups(['tag', 'node', 'nodes_sources'])]
    #[Serializer\Groups(['tag', 'node', 'nodes_sources'])]
    #[Gedmo\Versioned]
    protected ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'translatedTags')]
    #[ORM\JoinColumn(name: 'tag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected ?Tag $tag = null;

    #[ORM\ManyToOne(targetEntity: Translation::class, fetch: 'EXTRA_LAZY', inversedBy: 'tagTranslations')]
    #[ORM\JoinColumn(name: 'translation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Groups(['tag', 'node', 'nodes_sources'])]
    #[Serializer\Groups(['tag', 'node', 'nodes_sources'])]
    protected ?TranslationInterface $translation = null;

    /**
     * @var Collection<TagTranslationDocuments>
     * @Serializer\Exclude
     */
    #[ORM\OneToMany(
        mappedBy: 'tagTranslation',
        targetEntity: TagTranslationDocuments::class,
        cascade: ['persist', 'merge'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[SymfonySerializer\Ignore]
    protected Collection $tagTranslationDocuments;

    /**
     * Create a new TagTranslation with its origin Tag and Translation.
     *
     * @param Tag|null         $original
     * @param TranslationInterface|null $translation
     */
    public function __construct(Tag $original = null, TranslationInterface $translation = null)
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name): TagTranslation
    {
        $this->name = $name ?? '';

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
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription(?string $description): TagTranslation
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
     * @param Tag|null $tag the tag
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
     * @return TranslationInterface|null
     */
    public function getTranslation(): ?TranslationInterface
    {
        return $this->translation;
    }

    /**
     * Sets the value of translation.
     *
     * @param TranslationInterface|null $translation the translation
     *
     * @return self
     */
    public function setTranslation(?TranslationInterface $translation): TagTranslation
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
    #[SymfonySerializer\Groups(['tag'])]
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
     * @param Collection $tagTranslationDocuments
     * @return TagTranslation
     */
    public function setTagTranslationDocuments(Collection $tagTranslationDocuments): TagTranslation
    {
        $this->tagTranslationDocuments = $tagTranslationDocuments;
        return $this;
    }
}
