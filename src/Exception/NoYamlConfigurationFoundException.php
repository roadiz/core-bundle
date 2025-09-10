<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Exception;

/**
 * Exception raised when no yaml configuration file has been found.
 */
class NoYamlConfigurationFoundException extends NoConfigurationFoundException
{
    /**
     * @var string
     */
    protected $message = 'No configuration file was found. Make sure that conf/config.yml exists.';
}
