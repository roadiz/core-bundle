<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Translated representation of Folders.
 *
 * It stores their name.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\FolderTranslationRepository")
 * @ORM\Table(name="folders_translations", uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"folder_id", "translation_id"})
 * })
 * @UniqueEntity(fields={"folder", "translation"})
 */
class FolderTranslation extends AbstractEntity
{
    /**
     * @ORM\Column(type="string")
     * @Serializer\Groups({"folder", "document"})
     * @SymfonySerializer\Groups({"folder", "document"})
     * @Assert\Length(max=250)
     * @var string
     */
    protected string $name = '';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }
    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="translatedFolders")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Exclude
     * @SymfonySerializer\Ignore
     * @var Folder|null
     */
    protected ?Folder $folder = null;

    /**
     * @ORM\ManyToOne(targetEntity="Translation", inversedBy="folderTranslations", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="translation_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"folder", "document"})
     * @SymfonySerializer\Groups({"folder", "document"})
     * @var TranslationInterface|null
     */
    protected ?TranslationInterface $translation = null;

    /**
     * @param Folder $original
     * @param TranslationInterface $translation
     */
    public function __construct(Folder $original, TranslationInterface $translation)
    {
        $this->setFolder($original);
        $this->setTranslation($translation);
        $this->name = $original->getDirtyFolderName() != '' ? $original->getDirtyFolderName() : $original->getFolderName();
    }

    /**
     * @return Folder
     */
    public function getFolder(): Folder
    {
        return $this->folder;
    }

    /**
     * @param Folder $folder
     * @return FolderTranslation
     */
    public function setFolder(Folder $folder): FolderTranslation
    {
        $this->folder = $folder;
        return $this;
    }


    /**
     * Gets the value of translation.
     *
     * @return TranslationInterface
     */
    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    /**
     * Sets the value of translation.
     *
     * @param TranslationInterface $translation the translation
     * @return self
     */
    public function setTranslation(TranslationInterface $translation): FolderTranslation
    {
        $this->translation = $translation;
        return $this;
    }
}
