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
trait PatchNoEndpointTrait
{
    #[Test]
    public function patch_no_endpoint(): void
    {
        $item = $this->getFactory()->create();

        $browser = $this->browser()->actingAs(UserFactory::new()->with(['roles' => ['ROLE_SUPER_ADMIN']])->create());

        $browser
            ->patch(
                \sprintf('%s/%s', $this->getBaseUrl(), $item->getId()),
                [
                    'headers' => ['Content-Type' => 'application/merge-patch+json'],
                    'json' => [],
                ],
            )
            ->assertStatus(405)
        ;
    }
}
