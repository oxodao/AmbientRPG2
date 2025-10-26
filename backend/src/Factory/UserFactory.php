<?php

namespace App\Factory;

use App\Entity\User;
use App\Enum\Language;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
class UserFactory extends PersistentObjectFactory
{
    public function __construct(
        private readonly ?UserPasswordHasherInterface $passwordHasher = null,
    ) {
        parent::__construct();
    }

    protected function defaults(): callable
    {
        return fn () => [
            'username' => self::faker()->userName(),
            'email' => self::faker()->unique()->safeEmail(),
            'password' => 'password',
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

    protected function initialize(): static
    {
        return $this->afterInstantiate(function (User $user) {
            $pwd = $user->getPassword();

            if ($pwd && $this->passwordHasher) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
            }
        });
    }
}
