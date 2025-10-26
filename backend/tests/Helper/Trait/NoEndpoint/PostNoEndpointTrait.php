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
trait PostNoEndpointTrait
{
    #[Test]
    public function post_no_endpoint(): void
    {
        $browser = $this->browser()->actingAs(UserFactory::new()->with(['roles' => ['ROLE_SUPER_ADMIN']])->create());

        $browser
            ->post($this->getBaseUrl(), ['json' => []])
            ->assertStatus(405)
        ;
    }
}
