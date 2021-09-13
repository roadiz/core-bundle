<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use RZ\Roadiz\CoreBundle\Entity\Document;
use Symfony\Component\Validator\Constraint;

/**
 * @package RZ\Roadiz\CoreBundle\Form\Constraint
 */
class UniqueFilename extends Constraint
{
    /**
     * @var Document null
     */
    public $document = null;

    public $message = 'filename.alreadyExists';
}
