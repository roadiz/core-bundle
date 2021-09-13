<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MediaFinder;

use RZ\Roadiz\Utils\MediaFinders\AbstractSoundcloudEmbedFinder;

/**
 * Soundcloud tools class.
 */
class SoundcloudEmbedFinder extends AbstractSoundcloudEmbedFinder
{
    use EmbedFinderTrait;
}
