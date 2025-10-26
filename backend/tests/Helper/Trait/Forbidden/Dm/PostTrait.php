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
trait PostTrait
{
    #[Test]
    public function post_dm_forbidden(): void
    {
        $browser = $this->browser()->actingAs(UserFactory::new()->dm()->create());

        $browser
            ->post($this->getBaseUrl(), ['json' => []])
            ->assertStatus(403)
        ;
    }
}
