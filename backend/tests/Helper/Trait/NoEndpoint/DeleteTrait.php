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
trait DeleteTrait
{
    #[Test]
    public function delete_no_endpoint(): void
    {
        $item = $this->getFactory()->create();

        $browser = $this->browser()->actingAs(UserFactory::new()->with(['roles' => ['ROLE_SUPER_ADMIN']])->create());

        $browser
            ->delete(\sprintf('%s/%s', $this->getBaseUrl(), $item->getId()))
            ->assertStatus(405)
        ;
    }
}
