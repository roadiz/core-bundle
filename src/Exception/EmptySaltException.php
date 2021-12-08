<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Exception;

/**
 * Exception raised when a security method need a non-empty secret or salt.
 */
class EmptySaltException extends \Exception
{
}
