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
trait DeleteUnauthenticatedForbiddenTrait
{
    #[Test]
    public function delete_unauthenticated_forbidden(): void
    {
        $item = $this->getFactory()->create();

        $browser = $this->browser();

        $browser
            ->delete(\sprintf('%s/%s', $this->getBaseUrl(), $item->getId()))
            ->assertStatus(401)
        ;
    }
}
