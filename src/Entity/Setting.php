<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Settings entity are a simple key-value configuration system.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\SettingRepository")
 * @ORM\Table(name="settings", indexes={
 *     @ORM\Index(columns={"type"}),
 *     @ORM\Index(columns={"name"}),
 *     @ORM\Index(columns={"visible"})
 * })
 * @UniqueEntity(fields={"name"})
 */
class Setting extends AbstractEntity
{
    /**
     * Associates custom form field type to a readable string.
     *
     * These string will be used as translation key.
     *
     * @var array<int, string>
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    public static array $typeToHuman = [
        AbstractField::STRING_T => 'string.type',
        AbstractField::DATETIME_T => 'date-time.type',
        AbstractField::TEXT_T => 'text.type',
        AbstractField::MARKDOWN_T => 'markdown.type',
        AbstractField::BOOLEAN_T => 'boolean.type',
        AbstractField::INTEGER_T => 'integer.type',
        AbstractField::DECIMAL_T => 'decimal.type',
        AbstractField::EMAIL_T => 'email.type',
        AbstractField::DOCUMENTS_T => 'documents.type',
        AbstractField::COLOUR_T => 'colour.type',
        AbstractField::JSON_T => 'json.type',
        AbstractField::CSS_T => 'css.type',
        AbstractField::YAML_T => 'yaml.type',
        AbstractField::ENUM_T => 'single-choice.type',
        AbstractField::MULTIPLE_T => 'multiple-choice.type',
    ];

    /**
     * @ORM\Column(type="string", unique=true)
     * @Serializer\Groups({"setting", "nodes_sources"})
     * @SymfonySerializer\Groups({"setting", "nodes_sources"})
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length(max=250)
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
     *
     * @return $this
     */
    public function setName(?string $name)
    {
        $this->name = trim(\mb_strtolower($name ?? ''));
        $this->name = (new UnicodeString($this->name))
            ->ascii()
            ->toString();
        $this->name = preg_replace('#([^a-z])#', '_', $this->name);

        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(type="text", unique=false, nullable=true)
     * @Serializer\Groups({"setting"})
     * @SymfonySerializer\Groups({"setting"})
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
     *
     * @return Setting
     */
    public function setDescription(?string $description): Setting
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({"setting", "nodes_sources"})
     * @SymfonySerializer\Groups({"setting", "nodes_sources"})
     * @Serializer\Type("string")
     */
    private ?string $value = null;

    /**
     * Holds clear setting value after value is decoded by postLoad Doctrine event.
     *
     * READ ONLY: Not persisted value to hold clear value if setting is encrypted.
     *
     * @var string|null
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore
     */
    private ?string $clearValue = null;

    /**
     * @return string|null
     */
    public function getRawValue(): ?string
    {
        return $this->value;
    }

    /**
     * Getter for setting value OR clear value, if encrypted.
     *
     * @return string|bool|\DateTime|int|null
     * @SymfonySerializer\Ignore
     * @throws \Exception
     */
    public function getValue()
    {
        if ($this->isEncrypted()) {
            $value = $this->clearValue;
        } else {
            $value = $this->value;
        }

        if ($this->getType() == AbstractField::BOOLEAN_T) {
            return (bool) $value;
        }

        if (null !== $value) {
            if ($this->getType() == AbstractField::DATETIME_T) {
                return new \DateTime($value);
            }
            if ($this->getType() == AbstractField::DOCUMENTS_T) {
                return (int) $value;
            }
        }

        return $value;
    }
    /**
     * @param null|mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        if (null === $value) {
            $this->value = null;
        } elseif (
            ($this->getType() === AbstractField::DATETIME_T || $this->getType() === AbstractField::DATE_T) &&
            $value instanceof \DateTime
        ) {
            $this->value = $value->format('c'); // $value is instance of \DateTime
        } else {
            $this->value = (string) $value;
        }

        return $this;
    }

    /**
     * Holds clear setting value after value is decoded by postLoad Doctrine event.
     *
     * @param string|null $clearValue
     *
     * @return Setting
     */
    public function setClearValue(?string $clearValue): Setting
    {
        $this->clearValue = $clearValue;

        return $this;
    }

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" = true})
     * @Serializer\Groups({"setting"})
     * @SymfonySerializer\Groups({"setting"})
     * @Serializer\Type("bool")
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
     * @param bool $visible
     *
     * @return $this
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" = false})
     * @Serializer\Groups({"setting"})
     * @SymfonySerializer\Groups({"setting"})
     * @Serializer\Type("bool")
     */
    private bool $encrypted = false;

    /**
     * @return bool
     */
    public function isEncrypted(): bool
    {
        return $this->encrypted;
    }

    /**
     * @param bool $encrypted
     *
     * @return Setting
     */
    public function setEncrypted(bool $encrypted): Setting
    {
        $this->encrypted = $encrypted;

        return $this;
    }

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RZ\Roadiz\CoreBundle\Entity\SettingGroup",
     *     inversedBy="settings",
     *     cascade={"persist", "merge"},
     *     fetch="EAGER"
     * )
     * @ORM\JoinColumn(name="setting_group_id", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"setting"})
     * @SymfonySerializer\Groups({"setting"})
     * @Serializer\Type("RZ\Roadiz\CoreBundle\Entity\SettingGroup")
     * @Serializer\Accessor(getter="getSettingGroup", setter="setSettingGroup")
     * @Serializer\AccessType("public_method")
     * @var SettingGroup|null
     */
    private ?SettingGroup $settingGroup;

    /**
     * @return SettingGroup|null
     */
    public function getSettingGroup(): ?SettingGroup
    {
        return $this->settingGroup;
    }
    /**
     * @param SettingGroup|null $settingGroup
     *
     * @return $this
     */
    public function setSettingGroup(?SettingGroup $settingGroup)
    {
        $this->settingGroup = $settingGroup;

        return $this;
    }

    /**
     * Value types.
     * Use NodeTypeField types constants.
     *
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"setting"})
     * @SymfonySerializer\Groups({"setting"})
     * @Serializer\Type("int")
     */
    private int $type = AbstractField::STRING_T;

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return $this
     */
    public function setType(int $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Available values for ENUM and MULTIPLE setting types.
     *
     * @var string|null
     * @ORM\Column(name="defaultValues", type="text", nullable=true)
     * @Serializer\Groups({"setting"})
     * @SymfonySerializer\Groups({"setting"})
     * @Serializer\Type("string")
     */
    private ?string $defaultValues;

    /**
     * @return string|null
     */
    public function getDefaultValues(): ?string
    {
        return $this->defaultValues;
    }

    /**
     * @param string|null $defaultValues
     *
     * @return Setting
     */
    public function setDefaultValues(?string $defaultValues)
    {
        $this->defaultValues = $defaultValues;

        return $this;
    }
}
