<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MediaFinder;

use RZ\Roadiz\Documents\MediaFinders\AbstractYoutubeEmbedFinder;

class YoutubeEmbedFinder extends AbstractYoutubeEmbedFinder
{
    use EmbedFinderTrait;
}
