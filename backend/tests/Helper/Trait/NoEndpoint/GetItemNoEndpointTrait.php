<?php

namespace App\Tests\Helper\Trait\NoEndpoint;

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
trait GetItemNoEndpointTrait
{
    #[Test]
    public function get_item_no_endpoint(): void
    {
        $item = $this->getFactory()->create();

        $browser = $this->browser()->actingAs(UserFactory::new()->with(['roles' => ['ROLE_SUPER_ADMIN']])->create());

        $browser
            ->get(\sprintf('%s/%s', $this->getBaseUrl(), $item->getId()))
            ->assertStatus(405)
        ;
    }
}
