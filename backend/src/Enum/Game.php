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
enum Game: string implements TranslatableEnumLabelInterface
{
    use EnumApiResourceTrait;
    use TranslatableEnumTrait;

    case CYBERPUNK_RED = 'cyberpunk_red';
    case FALLOUT = 'fallout';
    case DND = 'dnd';
    case GENERIC = 'generic';
}
