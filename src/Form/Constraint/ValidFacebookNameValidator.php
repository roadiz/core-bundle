<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use RZ\Roadiz\Documents\MediaFinders\FacebookPictureFinder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @deprecated
 */
final class ValidFacebookNameValidator extends ConstraintValidator
{
    public function __construct(private readonly FacebookPictureFinder $facebookPictureFinder)
    {
    }

    /**
     * @param ValidFacebookName $constraint
     */
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ('' != $value) {
            if (0 === preg_match('#^[0-9]*$#', (string) $value)) {
                $this->context->addViolation($constraint->message);
            } else {
                /*
                 * Test if the username really exists.
                 */
                try {
                    $this->facebookPictureFinder->getPictureUrl($value);
                } catch (\Exception) {
                    $this->context->addViolation($constraint->message);
                }
            }
        }
    }
}
