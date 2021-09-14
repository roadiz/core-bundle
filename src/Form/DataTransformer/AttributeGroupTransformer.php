<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class AttributeGroupTransformer
 * @package RZ\Roadiz\CoreBundle\Form\DataTransformer
 */
class AttributeGroupTransformer implements DataTransformerInterface
{
    private $manager;

    /**
     * AttributeGroupTransformer constructor.
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param AttributeGroup|null $attributeGroup
     * @return int|string
     */
    public function transform($attributeGroup)
    {
        if (null === $attributeGroup) {
            return '';
        }
        return $attributeGroup->getId();
    }

    /**
     * @param mixed $attributeGroupId
     * @return null|AttributeGroup
     */
    public function reverseTransform($attributeGroupId)
    {
        if (!$attributeGroupId) {
            return null;
        }

        $attributeGroup = $this->manager
            ->getRepository(AttributeGroup::class)
            ->find($attributeGroupId)
        ;

        if (null === $attributeGroup) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'A attribute-group with id "%s" does not exist!',
                $attributeGroupId
            ));
        }

        return $attributeGroup;
    }
}
