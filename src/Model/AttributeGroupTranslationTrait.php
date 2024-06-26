<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait AttributeGroupTranslationTrait
{
    #[
        ORM\ManyToOne(targetEntity: "RZ\Roadiz\Core\AbstractEntities\TranslationInterface"),
        ORM\JoinColumn(name: "translation_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE"),
        Serializer\Groups(["attribute_group", "attribute", "node", "nodes_sources"]),
        Serializer\Type("RZ\Roadiz\Core\AbstractEntities\TranslationInterface"),
        Serializer\Accessor(getter: "getTranslation", setter: "setTranslation")
    ]
    protected TranslationInterface $translation;

    #[
        ORM\Column(type: "string", length: 255, unique: false, nullable: false),
        Serializer\Groups(["attribute_group", "attribute", "node", "nodes_sources"]),
        Serializer\Type("string"),
        Assert\Length(max: 255)
    ]
    protected string $name = '';

    #[
        ORM\ManyToOne(targetEntity: AttributeGroupInterface::class, cascade: ["persist"], inversedBy: "attributeGroupTranslations"),
        ORM\JoinColumn(name: "attribute_group_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE"),
        Serializer\Exclude
    ]
    protected AttributeGroupInterface $attributeGroup;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setName(string $value)
    {
        $this->name = $value;
        return $this;
    }

    /**
     * @param TranslationInterface $translation
     * @return self
     */
    public function setTranslation(TranslationInterface $translation)
    {
        $this->translation = $translation;
        return $this;
    }

    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    /**
     * @return AttributeGroupInterface
     */
    public function getAttributeGroup(): AttributeGroupInterface
    {
        return $this->attributeGroup;
    }

    /**
     * @param AttributeGroupInterface $attributeGroup
     * @return self
     */
    public function setAttributeGroup(AttributeGroupInterface $attributeGroup)
    {
        $this->attributeGroup = $attributeGroup;
        return $this;
    }
}
