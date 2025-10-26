<?php

namespace App\Factory;

use App\Entity\User;
use App\Enum\Language;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
class UserFactory extends PersistentObjectFactory
{
    protected function defaults(): callable
    {
        return fn () => [
            'username' => self::faker()->userName(),
            'email' => self::faker()->unique()->safeEmail(),
            'language' => Language::AMERICAN_ENGLISH,
            'roles' => ['ROLE_USER'],
        ];
    }

    public function withLanguage(Language $language): static
    {
        return $this->with(['language' => $language]);
    }

    public function admin(): self
    {
        return $this->with([
            'roles' => ['ROLE_ADMIN'],
        ]);
    }

    public static function class(): string
    {
        return User::class;
    }
}
