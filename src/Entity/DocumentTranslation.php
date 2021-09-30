<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Loggable;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\Models\DocumentInterface;

/**
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\DocumentTranslationRepository")
 * @ORM\Table(name="documents_translations", uniqueConstraints={@ORM\UniqueConstraint(columns={"document_id", "translation_id"})})
 * @Gedmo\Loggable(logEntryClass="RZ\Roadiz\CoreBundle\Entity\UserLogEntry")
 */
class DocumentTranslation extends AbstractEntity implements Loggable
{
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Gedmo\Versioned
     */
    protected ?string $name = null;
    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name): DocumentTranslation
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Gedmo\Versioned
     */
    protected ?string $description = null;

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
    public function setDescription(?string $description): DocumentTranslation
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Gedmo\Versioned
     * @var string|null
     */
    private ?string $copyright = null;

    /**
     * @return string|null
     */
    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    /**
     * @param string|null $copyright
     *
     * @return $this
     */
    public function setCopyright(?string $copyright): DocumentTranslation
    {
        $this->copyright = $copyright;

        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="RZ\Roadiz\CoreBundle\Entity\Translation", inversedBy="documentTranslations", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="translation_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @SymfonySerializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @var Translation|null
     */
    protected ?TranslationInterface $translation = null;

    /**
     * @return Translation
     */
    public function getTranslation(): Translation
    {
        return $this->translation;
    }

    /**
     * @param Translation $translation
     * @return $this
     */
    public function setTranslation(Translation $translation): DocumentTranslation
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Document", inversedBy="documentTranslations", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE")
     * @var DocumentInterface|null
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     */
    protected ?DocumentInterface $document;

    /**
     * @return DocumentInterface
     */
    public function getDocument(): DocumentInterface
    {
        return $this->document;
    }

    /**
     * @param DocumentInterface $document
     * @return $this
     */
    public function setDocument(DocumentInterface $document)
    {
        $this->document = $document;
        return $this;
    }
}
