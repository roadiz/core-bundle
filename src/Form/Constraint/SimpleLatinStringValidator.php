<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SimpleLatinStringValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($constraint instanceof SimpleLatinString) {
            if (null !== $value && preg_match('#[^a-z_\s\-]#', \mb_strtolower($value)) === 1) {
                $this->context->addViolation($constraint->message);
            }
        }
    }
}
