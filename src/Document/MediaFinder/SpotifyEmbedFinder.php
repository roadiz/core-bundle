<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MediaFinder;

use RZ\Roadiz\Utils\MediaFinders\AbstractSpotifyEmbedFinder;

class SpotifyEmbedFinder extends AbstractSpotifyEmbedFinder
{
    use EmbedFinderTrait;
}
