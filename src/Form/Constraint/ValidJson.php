<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class ValidJson extends Constraint
{
    public string $message = 'json.is.not.valid.{{ error }}';
}
