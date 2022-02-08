<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\CoreBundle\TwigExtension\TokenParser\TransChoiceTokenParser;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TokenParser\AbstractTokenParser;
use Twig\TwigFilter;

/**
 * @deprecated Just for BC
 */
final class TransChoiceExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('transchoice', [$this, 'transchoice']),
        ];
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return AbstractTokenParser[]
     */
    public function getTokenParsers()
    {
        return [
            // {% transchoice count %}
            //     {0} There is no apples|{1} There is one apple|]1,Inf] There is {{ count }} apples
            // {% endtranschoice %}
            new TransChoiceTokenParser(),
        ];
    }

    /**
     * @deprecated since Symfony 4.2, use the trans() method instead with a %count% parameter
     */
    public function transchoice($message, $count, array $arguments = [], $domain = null, $locale = null)
    {
        return $this->translator->trans($message, array_merge(['%count%' => $count], $arguments), $domain, $locale);
    }
}
