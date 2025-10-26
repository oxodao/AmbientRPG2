<?php

namespace App\Story;

use App\Enum\Game;
use App\Factory\CampaignFactory;
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
            'user__oxodao',
            UserFactory::new()->dm()->with([
                'username' => 'oxodao',
                'oauthUserId' => '720a5838-6c8c-435f-ab82-88726622eaaf',
            ])->create(),
            'user',
        );

        $this->addState(
            'user__matthieu',
            UserFactory::new()->dm()->with([
                'username' => 'matthieu',
                'oauthUserId' => '3f0867fb-8ad2-4543-9b03-455418f27e45',
            ])->create(),
            'user',
        );

        $this->addState(
            'user__user',
            UserFactory::new()->with([
                'username' => 'user',
            ])->create(),
            'user',
        );

        UserFactory::new()->many(5)->create();

        $this->addState(
            'campaign__cpr',
            CampaignFactory::new()->with([
                'title' => 'Oneshot 1',
                'game' => Game::CYBERPUNK_RED,
                'owner' => $this->getState('user__oxodao'),
            ]),
            'campaign',
        );

        $this->addState(
            'campaign__fallout',
            CampaignFactory::new()->with([
                'title' => 'Campagne 1',
                'game' => Game::FALLOUT,
                'owner' => $this->getState('user__matthieu'),
            ]),
            'campaign',
        );
    }
}
