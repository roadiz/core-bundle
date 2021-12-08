<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class SimpleLatinString extends Constraint
{
    public $message = 'string.should.only.contain.latin.characters';
}
