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
trait PostAdminForbiddenTrait
{
    #[Test]
    public function post_admin_forbidden(): void
    {
        $browser = $this->browser()->actingAs(UserFactory::new()->with(['roles' => ['ROLE_ADMIN']])->create());

        $browser
            ->post($this->getBaseUrl(), ['json' => []])
            ->assertStatus(403)
        ;
    }
}
