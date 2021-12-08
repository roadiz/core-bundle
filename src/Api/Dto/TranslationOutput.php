<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

final class TranslationOutput
{
    public string $name = '';
    public string $locale = '';
    public bool $available = false;
    public bool $defaultTranslation = false;
}
