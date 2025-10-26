<?php

namespace App\Behavior;

use ApiPlatform\Metadata\ApiProperty;
use App\ApiConfig\EnumApiConfig;
use Symfony\Component\Serializer\Attribute\Groups;

trait EnumApiResourceTrait
{
    #[Groups(EnumApiConfig::GET)]
    #[ApiProperty(identifier: true)]
    public function getId(): string
    {
        return $this->value;
    }

    #[Groups(EnumApiConfig::GET)]
    public function getName(): string
    {
        return $this->name;
    }

    #[Groups(EnumApiConfig::GET)]
    public function getValue(): int|string
    {
        return $this->value;
    }
}
