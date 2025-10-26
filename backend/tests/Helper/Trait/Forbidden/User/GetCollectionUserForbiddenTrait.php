<?php

namespace App\Tests\Helper\Trait\Forbidden\User;

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
trait GetCollectionUserForbiddenTrait
{
    #[Test]
    public function get_collection_user_forbidden(): void
    {
        $browser = $this->browser()->actingAs(UserFactory::new()->create());

        $browser
            ->get($this->getBaseUrl())
            ->assertStatus(403)
        ;
    }
}
