<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * Describes a complex ManyToMany relation
 * between TagTranslation and Documents.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\TagTranslationDocumentsRepository")
 * @ORM\Table(name="tags_translations_documents", indexes={
 *     @ORM\Index(columns={"position"}),
 *     @ORM\Index(columns={"tag_translation_id", "position"}, name="tagtranslation_position")
 * })
 */
class TagTranslationDocuments extends AbstractPositioned
{
    /**
     * @ORM\ManyToOne(targetEntity="RZ\Roadiz\CoreBundle\Entity\TagTranslation", inversedBy="tagTranslationDocuments", fetch="EAGER", cascade={"persist", "merge"})
     * @ORM\JoinColumn(name="tag_translation_id", referencedColumnName="id", onDelete="CASCADE")
     * @var TagTranslation|null
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore
     */
    protected ?TagTranslation $tagTranslation = null;

    /**
     * @ORM\ManyToOne(targetEntity="RZ\Roadiz\CoreBundle\Entity\Document", inversedBy="tagTranslations", fetch="EAGER", cascade={"persist", "merge"})
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Document|null
     * @Serializer\Groups({"tag"})
     * @SymfonySerializer\Groups({"tag"})
     * @Serializer\Type("RZ\Roadiz\CoreBundle\Entity\Document")
     */
    protected ?Document $document = null;

    /**
     * Create a new relation between NodeSource, a Document and a NodeTypeField.
     *
     * @param TagTranslation|null $tagTranslation
     * @param Document|null $document
     */
    public function __construct(TagTranslation $tagTranslation = null, Document $document = null)
    {
        $this->document = $document;
        $this->tagTranslation = $tagTranslation;
    }

    /**
     * @SymfonySerializer\Ignore
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->tagTranslation = null;
        }
    }

    /**
     * Gets the value of document.
     *
     * @return Document
     */
    public function getDocument(): ?Document
    {
        return $this->document;
    }

    /**
     * Sets the value of document.
     *
     * @param Document|null $document the document
     *
     * @return self
     */
    public function setDocument(?Document $document): TagTranslationDocuments
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return TagTranslation
     */
    public function getTagTranslation(): ?TagTranslation
    {
        return $this->tagTranslation;
    }

    /**
     * @param TagTranslation|null $tagTranslation
     * @return TagTranslationDocuments
     */
    public function setTagTranslation(?TagTranslation $tagTranslation): TagTranslationDocuments
    {
        $this->tagTranslation = $tagTranslation;
        return $this;
    }
}
