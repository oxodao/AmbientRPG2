<?php

namespace App\Factory;

use App\Entity\Campaign;
use App\Enum\Game;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Campaign>
 */
class CampaignFactory extends PersistentObjectFactory
{
    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->sentence(3),
            'game' => self::faker()->randomElement(Game::cases()),
            'owner' => UserFactory::new()->dm(),
        ];
    }

    public static function class(): string
    {
        return Campaign::class;
    }
}
