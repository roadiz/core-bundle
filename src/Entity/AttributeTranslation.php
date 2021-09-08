<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\CoreBundle\Model\AttributeTranslationInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeTranslationTrait;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;

/**
 * @package RZ\Roadiz\CoreBundle\Entity
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\AttributeTranslationRepository")
 * @ORM\Table(name="attribute_translations", indexes={
 *     @ORM\Index(columns={"label"})
 * }, uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"attribute_id", "translation_id"})
 * }))
 * @ORM\HasLifecycleCallbacks
 */
class AttributeTranslation extends AbstractEntity implements AttributeTranslationInterface
{
    use AttributeTranslationTrait;
}
