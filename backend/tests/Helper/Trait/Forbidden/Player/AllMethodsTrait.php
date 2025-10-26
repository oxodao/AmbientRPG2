<?php

namespace App\Tests\Helper\Trait\Forbidden\Player;

use App\Tests\ApiTestCase;

/**
 * @template T of object
 *
 * @mixin ApiTestCase<T>
 *
 * @phpstan-require-extends ApiTestCase
 */
trait AllMethodsTrait
{
    /** @use GetCollectionTrait<T> */
    use GetCollectionTrait;

    /** @use GetItemTrait<T> */
    use GetItemTrait;

    /** @use PostTrait<T> */
    use PostTrait;

    /** @use PatchTrait<T> */
    use PatchTrait;

    /** @use DeleteTrait<T> */
    use DeleteTrait;
}
