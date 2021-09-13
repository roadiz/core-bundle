<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class UniqueTagName extends Constraint
{
    public $currentValue = null;
    public $message = 'tagName.%name%.alreadyExists';
}
