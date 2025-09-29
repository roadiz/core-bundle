<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception raised when you want to force a given Response object.
 */
class ForceResponseException extends \Exception
{
    protected Response $response;

    public function __construct(Response $response)
    {
        parent::__construct('Forcing response…', 1);
        $this->response = $response;
    }

    /**
     * Gets the value of response.
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Sets the value of response.
     *
     * @param Response $response the response
     *
     * @return self
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }
}
