<?php

namespace App\Behavior;

use App\ApiConfig\EnumApiConfig;
use Symfony\Component\Serializer\Attribute\Groups;

trait TranslatableEnumTrait
{
    #[Groups([EnumApiConfig::GET])]
    public function getLabel(): string
    {
        return \sprintf('%s.%s', \strtolower(new \ReflectionClass($this)->getShortName()), $this->value);
    }
}
