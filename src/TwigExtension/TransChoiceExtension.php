<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TransChoiceExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('transchoice', [$this, 'transchoice']),
        ];
    }
    
    public function transchoice(string $message)
    {
        return $message;
    }
}
