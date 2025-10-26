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
trait DeleteTrait
{
    #[Test]
    public function delete_player_notfound(): void
    {
        $item = $this->getFactory()->create();

        $browser = $this->browser()->actingAs(UserFactory::new()->with(['roles' => ['ROLE_PLAYER']])->create());

        $browser
            ->delete(\sprintf('%s/%s', $this->getBaseUrl(), $item->getId()))
            ->assertStatus(404)
        ;
    }
}
