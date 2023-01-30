<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Exception;

/**
 * Exception raised when trying to create a node-type field with
 * a reserved name.
 */
class ReservedSQLWordException extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'You tried to use a MySQL reserved word as a column name. Choose another one.';
}
