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
trait PatchTrait
{
    #[Test]
    public function patch_dm_forbidden(): void
    {
        $item = $this->getFactory()->create();

        $browser = $this->browser()->actingAs(UserFactory::new()->dm()->create());

        $browser
            ->patch(
                \sprintf('%s/%s', $this->getBaseUrl(), $item->getId()),
                [
                    'headers' => ['Content-Type' => 'application/merge-patch+json'],
                    'json' => [],
                ],
            )
            ->assertStatus(403)
        ;
    }
}
