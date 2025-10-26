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
trait GetCollectionNoEndpointTrait
{
    #[Test]
    public function get_collection_no_endpoint(): void
    {
        $browser = $this->browser()->actingAs(UserFactory::new()->with(['roles' => ['ROLE_SUPER_ADMIN']])->create());

        $browser
            ->get($this->getBaseUrl())
            ->assertStatus(405)
        ;
    }
}
