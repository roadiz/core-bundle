<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueTagNameValidator extends ConstraintValidator
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($this->isMulti($value)) {
            $names = explode(',', $value);
            foreach ($names as $name) {
                $name = strip_tags(trim($name));
                $this->testSingleValue($name, $constraint);
            }
        } else {
            $this->testSingleValue($value, $constraint);
        }
    }

    /**
     * @param string|null $value
     * @param Constraint $constraint
     */
    protected function testSingleValue($value, Constraint $constraint)
    {
        $value = StringHandler::slugify($value ?? '');

        /*
         * If value is already the node name
         * do nothing.
         */
        if (null !== $constraint->currentValue && $value == $constraint->currentValue) {
            return;
        }

        if (true === $this->tagNameExists($value)) {
            $this->context->addViolation($constraint->message, [
                '%name%' => $value,
            ]);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function tagNameExists($name)
    {
        $entity = $this->managerRegistry->getRepository(Tag::class)->findOneByTagName($name);

        return (null !== $entity);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    protected function isMulti($value)
    {
        return (bool) strpos($value ?? '', ',');
    }
}
