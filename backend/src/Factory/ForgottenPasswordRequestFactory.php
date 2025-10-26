<?php

namespace App\Factory;

use App\Entity\ForgottenPasswordRequest;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ForgottenPasswordRequest>
 */
class ForgottenPasswordRequestFactory extends PersistentObjectFactory
{
    protected function defaults(): array|callable
    {
        return fn () => [
            'user' => UserFactory::new(),
            'requestedAt' => ($rqAt = new \DateTimeImmutable()),
            'expiresAt' => $rqAt->modify('+7 day'),
            'requestedFromIp' => self::faker()->ipv4(),
            'code' => \bin2hex(\random_bytes(32)),
        ];
    }

    public function expired(): self
    {
        return $this->with([
            'expiresAt' => new \DateTimeImmutable()->modify('-1 day'),
        ]);
    }

    public static function class(): string
    {
        return ForgottenPasswordRequest::class;
    }
}
