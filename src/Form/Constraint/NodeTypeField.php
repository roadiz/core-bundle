<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class NodeTypeField extends Constraint
{
    public $message = 'default_values_do_not_match_field_type';
}
