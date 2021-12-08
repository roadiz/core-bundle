<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

interface AttributeGroupTranslationInterface extends PersistableInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $value
     *
     * @return mixed
     */
    public function setName(string $value);

    /**
     * @param TranslationInterface $translation
     *
     * @return mixed
     */
    public function setTranslation(TranslationInterface $translation);

    /**
     * @return TranslationInterface|null
     */
    public function getTranslation(): ?TranslationInterface;

    /**
     * @return AttributeGroupInterface
     */
    public function getAttributeGroup(): AttributeGroupInterface;

    /**
     * @param AttributeGroupInterface $attributeGroup
     *
     * @return mixed
     */
    public function setAttributeGroup(AttributeGroupInterface $attributeGroup);
}
