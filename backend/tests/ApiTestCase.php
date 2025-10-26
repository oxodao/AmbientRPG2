<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as ApiPlatformApiTestCase;
use Zenstruck\Browser;
use Zenstruck\Browser\KernelBrowser;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Test\Factories;

/**
 * @template T of object
 */
abstract class ApiTestCase extends ApiPlatformApiTestCase
{
    use Factories;
    use HasBrowser {
        browser as baseKernelBrowser;
    }

    abstract public function getBaseUrl(): string;

    /**
     * @return PersistentObjectFactory<T>
     */
    abstract public function getFactory(): PersistentObjectFactory;

    /**
     * @param array<mixed> $options
     * @param array<mixed> $server
     *
     * @return HydraApiBrowser
     */
    protected function browser(array $options = [], array $server = []): KernelBrowser
    {
        // @phpstan-ignore-next-line
        return $this->baseKernelBrowser($options, $server)->setDefaultHttpOptions(
            Browser\HttpOptions::create()->withHeader('Accept', 'application/ld+json'),
        );
    }
}
