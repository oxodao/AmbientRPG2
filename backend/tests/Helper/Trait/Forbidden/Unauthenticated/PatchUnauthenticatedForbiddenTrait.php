<?php

namespace App\Tests\Helper\Trait\Forbidden\Unauthenticated;

use App\Tests\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @template T of object
 *
 * @mixin ApiTestCase<T>
 *
 * @phpstan-require-extends ApiTestCase
 */
trait PatchUnauthenticatedForbiddenTrait
{
    #[Test]
    public function patch_unauthenticated_forbidden(): void
    {
        $item = $this->getFactory()->create();

        $browser = $this->browser();

        $browser
            ->patch(
                \sprintf('%s/%s', $this->getBaseUrl(), $item->getId()),
                [
                    'headers' => ['Content-Type' => 'application/merge-patch+json'],
                    'json' => [],
                ],
            )
            ->assertStatus(401)
        ;
    }
}
