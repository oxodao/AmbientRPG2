<?php

namespace App\Tests\Helper\Trait\Forbidden\Admin;

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
trait GetCollectionAdminForbiddenTrait
{
    #[Test]
    public function get_collection_admin_forbidden(): void
    {
        $browser = $this->browser()->actingAs(UserFactory::new()->with(['roles' => ['ROLE_ADMIN']])->create());

        $browser
            ->get($this->getBaseUrl())
            ->assertStatus(403)
        ;
    }
}
