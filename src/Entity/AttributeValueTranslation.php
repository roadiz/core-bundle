<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationTrait;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;

/**
 * @package RZ\Roadiz\CoreBundle\Entity
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\AttributeValueTranslationRepository")
 * @ORM\Table(name="attribute_value_translations", indexes={
 *     @ORM\Index(columns={"value"}),
 *     @ORM\Index(columns={"translation_id", "attribute_value"})
 * })
 * @ORM\HasLifecycleCallbacks
 */
class AttributeValueTranslation extends AbstractEntity implements AttributeValueTranslationInterface
{
    use AttributeValueTranslationTrait;
}
