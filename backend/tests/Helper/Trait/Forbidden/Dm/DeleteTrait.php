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
trait DeleteTrait
{
    #[Test]
    public function delete_dm_forbidden(): void
    {
        $item = $this->getFactory()->create();

        $browser = $this->browser()->actingAs(UserFactory::new()->dm()->create());

        $browser
            ->delete(\sprintf('%s/%s', $this->getBaseUrl(), $item->getId()))
            ->assertStatus(403)
        ;
    }
}
