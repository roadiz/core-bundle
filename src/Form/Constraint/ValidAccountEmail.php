<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class ValidAccountEmail extends Constraint
{
    public $message = '%email%.email.does.not.exist.in.user.account.database';
}
