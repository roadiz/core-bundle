<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MediaFinder;

use RZ\Roadiz\Utils\MediaFinders\AbstractDailymotionEmbedFinder;

/**
 * Dailymotion tools class.
 */
class DailymotionEmbedFinder extends AbstractDailymotionEmbedFinder
{
    use EmbedFinderTrait;
}
