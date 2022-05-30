<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use Doctrine\Common\Collections\Collection;

interface BlocksAwareWebResponseInterface extends WebResponseInterface
{
    /**
     * @return Collection|null
     */
    public function getBlocks(): ?Collection;

    /**
     * @param Collection|null $blocks
     * @return BlocksAwareWebResponseInterface
     */
    public function setBlocks(?Collection $blocks): BlocksAwareWebResponseInterface;
}
