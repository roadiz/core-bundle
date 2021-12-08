<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\UrlAlias;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package RZ\Roadiz\CoreBundle\Event
 */
abstract class FilterUrlAliasEvent extends Event
{
    protected $urlAlias;

    public function __construct(UrlAlias $urlAlias)
    {
        $this->urlAlias = $urlAlias;
    }

    public function getUrlAlias()
    {
        return $this->urlAlias;
    }
}
