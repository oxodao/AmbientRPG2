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
trait PostTrait
{
    #[Test]
    public function post_unauthenticated_forbidden(): void
    {
        $browser = $this->browser();

        $browser
            ->post($this->getBaseUrl(), ['json' => []])
            ->assertStatus(401)
        ;
    }
}
