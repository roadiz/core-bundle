<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use RZ\Roadiz\CoreBundle\Entity\Font;
use RZ\Roadiz\Core\Handlers\AbstractHandler;

/**
 * Handle operations with fonts entities.
 */
class FontHandler extends AbstractHandler
{
    protected ?Font $font = null;

    /**
     * @return Font
     */
    public function getFont(): Font
    {
        if (null === $this->font) {
            throw new \BadMethodCallException('Font is null');
        }
        return $this->font;
    }

    /**
     * @param Font $font
     * @return FontHandler
     */
    public function setFont(Font $font)
    {
        $this->font = $font;
        return $this;
    }
}
