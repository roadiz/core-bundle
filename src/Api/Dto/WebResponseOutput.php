<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\TreeWalker\WalkerInterface;
use Symfony\Component\Serializer\Annotation\Groups;

final class WebResponseOutput implements WebResponseInterface
{
    /**
     * @var PersistableInterface|null
     * @Groups({"web_response"})
     */
    public ?PersistableInterface $item = null;
    /**
     * @var null
     * @Groups({"web_response"})
     */
    public $breadcrumbs = null;
    /**
     * @var null
     * @Groups({"web_response"})
     */
    public $head = null;
    /**
     * @var WalkerInterface|null
     * @Groups({"web_response"})
     */
    public ?WalkerInterface $blocks = null;
}
