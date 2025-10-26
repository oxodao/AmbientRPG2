<?php

namespace App\Enum;

use ApiPlatform\Metadata\ApiResource;
use App\ApiConfig\EnumApiConfig;
use App\Behavior\EnumApiResourceTrait;
use App\Behavior\TranslatableEnumLabelInterface;
use App\Behavior\TranslatableEnumTrait;

#[ApiResource(
    cacheHeaders: EnumApiConfig::CACHE_HEADERS,
    normalizationContext: EnumApiConfig::NORMALIZATION_CONTEXT,
)]
enum Language: string implements TranslatableEnumLabelInterface
{
    use EnumApiResourceTrait;
    use TranslatableEnumTrait;
    case AMERICAN_ENGLISH = 'en_US';
    case FRENCH = 'fr_FR';

    public static function fromAlpha2(?string $locale): ?Language
    {
        return match ($locale) {
            'fr' => self::FRENCH,
            'en' => self::AMERICAN_ENGLISH,
            default => null,
        };
    }

    public function getLocale(): string
    {
        return match ($this) {
            self::FRENCH => 'fr',
            self::AMERICAN_ENGLISH => 'en',
        };
    }
}
