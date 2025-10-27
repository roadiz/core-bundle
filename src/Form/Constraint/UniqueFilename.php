<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Validator\Constraint;

final class UniqueFilename extends Constraint
{
    public ?DocumentInterface $document = null;

    public string $message = 'filename.alreadyExists';
}
