<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MediaFinder;

use RZ\Roadiz\Utils\MediaFinders\AbstractUnsplashPictureFinder;

class UnsplashPictureFinder extends AbstractUnsplashPictureFinder
{
    use EmbedFinderTrait;
}
