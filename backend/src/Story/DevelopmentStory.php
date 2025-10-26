<?php

namespace App\Story;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'development')]
final class DevelopmentStory extends Story
{
    public function build(): void
    {
        $this->addState(
            'user__admin',
            UserFactory::new()->admin()->with([
                'username' => 'admin',
            ])->create(),
            'user',
        );

        $this->addState(
            'user__user',
            UserFactory::new()->admin()->with([
                'username' => 'user',
            ])->create(),
            'user',
        );

        UserFactory::new()->many(5)->create();
    }
}
