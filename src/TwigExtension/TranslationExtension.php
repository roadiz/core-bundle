<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Locales;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

final class TranslationExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('country_iso', [$this, 'getCountryName']),
            new TwigFilter('locale_iso', [$this, 'getLocaleName']),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('rtl', [$this, 'isLocaleRtl'])
        ];
    }

    /**
     * @param mixed $mixed
     *
     * @return bool
     */
    public function isLocaleRtl($mixed)
    {
        if ($mixed instanceof TranslationInterface) {
            return $mixed->isRtl();
        }

        if (is_string($mixed)) {
            return in_array($mixed, Translation::getRightToLeftLocales());
        }

        return false;
    }

    /**
     * @param string $iso
     * @param string|null $locale
     * @return string
     */
    public function getCountryName(string $iso, ?string $locale = null): string
    {
        return Countries::getName($iso, $locale);
    }

    /**
     * @param string      $iso
     * @param string|null $locale
     *
     * @return string
     */
    public function getLocaleName(string $iso, ?string $locale = null): string
    {
        return Locales::getName($iso, $locale);
    }
}
