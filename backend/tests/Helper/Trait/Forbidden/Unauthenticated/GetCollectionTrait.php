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
trait GetCollectionTrait
{
    #[Test]
    public function get_collection_unauthenticated_forbidden(): void
    {
        $browser = $this->browser();

        $browser
            ->get($this->getBaseUrl())
            ->assertStatus(401)
        ;
    }
}
