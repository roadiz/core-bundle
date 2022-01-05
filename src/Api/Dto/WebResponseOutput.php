<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsInterface;
use RZ\Roadiz\CoreBundle\Api\Model\NodesSourcesHeadInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\TreeWalker\WalkerInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

final class WebResponseOutput implements WebResponseInterface
{
    /**
     * @var PersistableInterface|null
     * @Serializer\Groups({"web_response"})
     */
    public ?PersistableInterface $item = null;
    /**
     * @var BreadcrumbsInterface|null
     * @Serializer\Groups({"web_response"})
     */
    public ?BreadcrumbsInterface $breadcrumbs = null;
    /**
     * @var NodesSourcesHeadInterface|null
     * @Serializer\Groups({"web_response"})
     */
    public ?NodesSourcesHeadInterface $head = null;
    /**
     * @var Collection<WalkerInterface>|null
     * @Serializer\Groups({"web_response"})
     */
    public ?Collection $blocks = null;
}
