<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MediaFinder;

use RZ\Roadiz\Utils\MediaFinders\AbstractVimeoEmbedFinder;

/**
 * Vimeo tools class.
 */
class VimeoEmbedFinder extends AbstractVimeoEmbedFinder
{
    use EmbedFinderTrait;
}
