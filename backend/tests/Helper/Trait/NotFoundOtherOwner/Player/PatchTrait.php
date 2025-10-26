<?php

namespace App\Tests\Helper\Trait\NotFoundOtherOwner\Player;

use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @template T of object
 *
 * @mixin ApiTestCase<T>
 *
 * @phpstan-require-extends ApiTestCase
 */
trait PatchTrait
{
    #[Test]
    public function patch_player_notfound(): void
    {
        $item = $this->getFactory()->create();

        $browser = $this->browser()->actingAs(UserFactory::new()->with(['roles' => ['ROLE_PLAYER']])->create());

        $browser
            ->patch(
                \sprintf('%s/%s', $this->getBaseUrl(), $item->getId()),
                [
                    'headers' => ['Content-Type' => 'application/merge-patch+json'],
                    'json' => [],
                ],
            )
            ->assertStatus(404)
        ;
    }
}
