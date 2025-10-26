<?php

namespace App\Tests\Api;

use App\Entity\Campaign;
use App\Enum\Game;
use App\Factory\CampaignFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use App\Tests\Helper\Trait\Forbidden\Player as ForbiddenPlayer;
use App\Tests\Helper\Trait\Forbidden\Unauthenticated as ForbiddenUnauthenticated;
use App\Tests\Helper\Trait\NotFoundOtherOwner\Dm as NotFoundDm;
use App\Tests\Helper\Trait\NotFoundOtherOwner\Player as NotFoundPlayer;
use App\Tests\HydraApiBrowser;
use PHPUnit\Framework\Attributes\Test;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends ApiTestCase<Campaign>
 */
class CampaignApiTest extends ApiTestCase
{
    /** @use ForbiddenUnauthenticated\AllMethodsTrait<Campaign> */
    use ForbiddenUnauthenticated\AllMethodsTrait;

    /** @use ForbiddenPlayer\GetCollectionTrait<Campaign> */
    use ForbiddenPlayer\GetCollectionTrait;

    /** @use ForbiddenPlayer\PostTrait<Campaign> */
    use ForbiddenPlayer\PostTrait;

    /** @use NotFoundPlayer\GetItemTrait<Campaign> */
    use NotFoundPlayer\GetItemTrait;

    /** @use NotFoundPlayer\PatchTrait<Campaign> */
    use NotFoundPlayer\PatchTrait;

    /** @use NotFoundPlayer\DeleteTrait<Campaign> */
    use NotFoundPlayer\DeleteTrait;

    /**
     * DM cant get the campaigns of other DMs.
     *
     * @use NotFoundDm\GetItemTrait<Campaign>
     */
    use NotFoundDm\GetItemTrait;

    /**
     * DM cant patch the campaigns of other DMs.
     *
     * @use NotFoundDm\PatchTrait<Campaign>
     */
    use NotFoundDm\PatchTrait;

    /**
     * DM cant delete the campaigns of other DMs.
     *
     * @use NotFoundDm\DeleteTrait<Campaign>
     */
    use NotFoundDm\DeleteTrait;

    #[Test]
    public function campaign_getco_dm(): void
    {
        $dm1 = UserFactory::new()->dm()->create();
        $dm2 = UserFactory::new()->dm()->create();

        $campaigns = CampaignFactory::new()->many(3)->create(['owner' => $dm1]);
        CampaignFactory::new()->many(2)->create(['owner' => $dm2]);

        $this->browser()
            ->actingAs($dm1)
            ->get($this->getBaseUrl())
            ->assertStatus(200)
            ->assertJsonCollectionSchemaOk([
                'id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'game' => HydraApiBrowser::buildEnum('games'),
            ], 3)
            // Array reverse because the API returns items in DESC order
            ->assertCollectionResponseMatches(\array_reverse($campaigns), fn (Campaign $campaign) => [
                'id' => $campaign->getId(),
                'title' => $campaign->getTitle(),
                'game' => [
                    'id' => $campaign->getGame()->value,
                    'name' => $campaign->getGame()->getName(),
                    'value' => $campaign->getGame()->value,
                ],
            ])
        ;
    }

    #[Test]
    public function campaign_get_dm(): void
    {
        $campaign = $this->getFactory()->create();

        $this->browser()
            ->actingAs($campaign->getOwner())
            ->get(\sprintf('%s/%s', $this->getBaseUrl(), $campaign->getId()))
            ->assertStatus(200)
            ->assertJsonItemSchemaOk([
                'id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'game' => HydraApiBrowser::buildEnum('games'),
            ])
            ->assertResponseMatches([
                'id' => $campaign->getId(),
                'title' => $campaign->getTitle(),
                // Too lazy to handle translation so as long as the label is in it we'll say its ok
                'game' => [
                    'id' => $campaign->getGame()->value,
                    'name' => $campaign->getGame()->getName(),
                    'value' => $campaign->getGame()->value,
                ],
            ])
        ;
    }

    #[Test]
    public function campaign_patch_dm(): void
    {
        $campaign = $this->getFactory()->with([
            'title' => 'Old Campaign Title',
            'game' => Game::CYBERPUNK_RED,
        ])->create();

        $newTitle = 'New Campaign Title';
        $newGame = Game::FALLOUT;

        $this->browser()
            ->actingAs($campaign->getOwner())
            ->patch(\sprintf('%s/%s', $this->getBaseUrl(), $campaign->getId()), [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'json' => [
                    'title' => $newTitle,
                    'game' => \sprintf('/api/games/%s', $newGame->value),
                ],
            ])
            ->assertStatus(200)
            ->assertJsonItemSchemaOk([
                'id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'game' => HydraApiBrowser::buildEnum('games'),
            ])
            ->assertResponseMatches([
                'id' => $campaign->getId(),
                'title' => $newTitle,
                'game' => [
                    'id' => $newGame->value,
                    'name' => $newGame->getName(),
                    'value' => $newGame->value,
                ],
            ])
        ;
    }

    #[Test]
    public function campaign_delete_dm(): void
    {
        $campaign = $this->getFactory()->create();

        $this->browser()
            ->actingAs($campaign->getOwner())
            ->delete(\sprintf('%s/%s', $this->getBaseUrl(), $campaign->getId()))
            ->assertStatus(204)
        ;
    }

    public function getBaseUrl(): string
    {
        return '/api/campaigns';
    }

    /**
     * @return CampaignFactory
     */
    public function getFactory(): PersistentObjectFactory
    {
        return CampaignFactory::new();
    }
}
