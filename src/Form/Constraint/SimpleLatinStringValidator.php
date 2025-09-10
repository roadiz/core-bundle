<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SimpleLatinStringValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof SimpleLatinString) {
            if (null !== $value && 1 === preg_match('#[^a-z_\s\-]#', \mb_strtolower($value))) {
                $this->context->addViolation($constraint->message);
            }
        }
    }
}
