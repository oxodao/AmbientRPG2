<?php

namespace App\Tests\Helper\Trait\Forbidden\Dm;

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
trait GetItemTrait
{
    #[Test]
    public function get_item_dm_forbidden(): void
    {
        $item = $this->getFactory()->create();

        $browser = $this->browser()->actingAs(UserFactory::new()->dm()->create());

        $browser
            ->get(\sprintf('%s/%s', $this->getBaseUrl(), $item->getId()))
            ->assertStatus(403)
        ;
    }
}
