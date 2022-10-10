<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Loggable;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Repository\DocumentTranslationRepository;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\Models\DocumentInterface;

#[
    ORM\Entity(repositoryClass: DocumentTranslationRepository::class),
    ORM\Table(name: "documents_translations"),
    ORM\UniqueConstraint(columns: ["document_id", "translation_id"]),
    Gedmo\Loggable(logEntryClass: UserLogEntry::class)
]
class DocumentTranslation extends AbstractEntity implements Loggable
{
    /**
     * @var string|null
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Gedmo\Versioned
     */
    #[ORM\Column(type: 'string', nullable: true)]
    #[SymfonySerializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
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
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Gedmo\Versioned
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[SymfonySerializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
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
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Gedmo\Versioned
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[SymfonySerializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
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
     * @var string|null
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @Gedmo\Versioned
     */
    #[ORM\Column(type: 'text', length: 2000, nullable: true)]
    #[SymfonySerializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    protected ?string $externalUrl = null;

    /**
     * @return string|null
     */
    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    /**
     * @param string|null $externalUrl
     * @return DocumentTranslation
     */
    public function setExternalUrl(?string $externalUrl): DocumentTranslation
    {
        $this->externalUrl = $externalUrl;
        return $this;
    }

    /**
     * @Serializer\Groups({"document", "nodes_sources", "tag", "attribute"})
     * @var TranslationInterface|null
     */
    #[ORM\ManyToOne(targetEntity: 'RZ\Roadiz\CoreBundle\Entity\Translation', inversedBy: 'documentTranslations', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(name: 'translation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    protected ?TranslationInterface $translation = null;

    /**
     * @return TranslationInterface
     */
    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    /**
     * @param TranslationInterface $translation
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): DocumentTranslation
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * @var DocumentInterface|null
     * @Serializer\Exclude
     */
    #[ORM\ManyToOne(targetEntity: 'Document', inversedBy: 'documentTranslations', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Ignore]
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
