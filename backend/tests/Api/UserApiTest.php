<?php

namespace App\Tests\Api;

use App\Entity\User;
use App\Enum\Language;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use App\Tests\Helper\Trait\Forbidden\Unauthenticated\GetCollectionUnauthenticatedForbiddenTrait;
use App\Tests\Helper\Trait\Forbidden\Unauthenticated\GetItemUnauthenticatedForbiddenTrait;
use App\Tests\Helper\Trait\Forbidden\User\GetCollectionUserForbiddenTrait;
use App\Tests\Helper\Trait\Forbidden\User\GetItemUserForbiddenTrait;
use App\Tests\Helper\Trait\Forbidden\User\PatchUserForbiddenTrait;
use App\Tests\Helper\Trait\NoEndpoint\DeleteNoEndpointTrait;
use App\Tests\Helper\Trait\NoEndpoint\PostNoEndpointTrait;
use PHPUnit\Framework\Attributes\Test;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends ApiTestCase<User>
 */
class UserApiTest extends ApiTestCase
{
    /* User cannot edit other users */
    /** @use PatchUserForbiddenTrait<User> */
    use PatchUserForbiddenTrait;

    /* We do not have an endpoint for create */
    /** @use PostNoEndpointTrait<User> */
    use PostNoEndpointTrait;

    /* We do not have an endpoint for delete */
    /** @use DeleteNoEndpointTrait<User> */
    use DeleteNoEndpointTrait;

    /* Unauthenticated users cannot view user details */
    /** @use GetItemUnauthenticatedForbiddenTrait<User> */
    use GetItemUnauthenticatedForbiddenTrait;

    /* Unauthenticated users cannot view the list of users */
    /** @use GetCollectionUnauthenticatedForbiddenTrait<User> */
    use GetCollectionUnauthenticatedForbiddenTrait;

    /* Users cannot view other users' details */
    /** @use GetItemUserForbiddenTrait<User> */
    use GetItemUserForbiddenTrait;

    /* User cannot view the list of users */
    /** @use GetCollectionUserForbiddenTrait<User> */
    use GetCollectionUserForbiddenTrait;

    // Once all those traits have passed, we just need to verify
    // - A user can get its own details
    // - An admin can get any user's details
    // - An admin can get the list of users
    // - A user can edit its own details
    // - An admin can edit any user
    // - Email and Username are unique

    #[Test]
    public function user_get_own(): void
    {
        /** @var User $user */
        $user = $this->getFactory()->create();

        $this->browser()->actingAs($user)
            ->get(\sprintf('%s/%s', $this->getBaseUrl(), $user->getId()))
            ->assertStatus(200)
            ->assertJsonItemSchemaOk([
                'id' => ['type' => 'integer'],
                'username' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'language' => ['type' => 'string'],
                'roles' => ['type' => 'array', 'items' => ['type' => 'string']],
            ])
            ->assertResponseMatches([
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'language' => \sprintf('/api/languages/%s', $user->getLanguage()->value),
                'roles' => $user->getRoles(),
            ])
        ;
    }

    #[Test]
    public function user_edit_own(): void
    {
        /** @var User $user */
        $user = $this->getFactory()->with([
            'email' => 'oldemail@ambientrpg.dev',
            'language' => Language::AMERICAN_ENGLISH,
        ])->create();

        // Thanks to the new autorefresh we can't do nice stuff as before...
        $oldUsername = $user->getUsername();
        $oldRoles = $user->getRoles();

        $this->browser()->actingAs($user)
            ->patch(\sprintf('%s/%s', $this->getBaseUrl(), $user->getId()), [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'json' => [
                    'username' => 'new_username',
                    'email' => 'newemail@ambientrpg.dev',
                    'language' => '/api/languages/fr_FR',
                    'roles' => ['ROLE_ADMIN'],
                ],
            ])
            ->assertStatus(200)
            ->assertJsonItemSchemaOk([
                'id' => ['type' => 'integer'],
                'username' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'language' => ['type' => 'string'],
                'roles' => ['type' => 'array', 'items' => ['type' => 'string']],
            ])
            ->assertResponseMatches([
                'id' => $user->getId(),
                'username' => $oldUsername,
                'email' => 'newemail@ambientrpg.dev',
                'language' => \sprintf('/api/languages/%s', Language::FRENCH->value),
                'roles' => $oldRoles,
            ])
        ;
    }

    #[Test]
    public function edit_own_duplicate_email(): void
    {
        $this->getFactory()->with(['email' => 'email@ambientrpg.dev'])->create();

        /** @var User $user */
        $user = $this->getFactory()->with([
            'email' => 'some.email@ambientrpg.dev',
            'language' => Language::AMERICAN_ENGLISH,
        ])->create();

        $user->setEmail('email@ambientrpg.dev');

        $this->browser()->actingAs($user)
            ->patch(\sprintf('%s/%s', $this->getBaseUrl(), $user->getId()), [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'json' => ['email' => 'email@ambientrpg.dev'],
            ])
            ->assertStatus(422)
            ->assertValidationError([
                'Email already taken',
            ])
        ;
    }

    #[Test]
    public function admin_get(): void
    {
        /** @var User $admin */
        $admin = $this->getFactory()->admin()->create();

        /** @var User $user */
        $user = $this->getFactory()->create();

        $this->browser()->actingAs($admin)
            ->get(\sprintf('%s/%s', $this->getBaseUrl(), $user->getId()))
            ->assertStatus(200)
            ->assertJsonItemSchemaOk([
                'id' => ['type' => 'integer'],
                'username' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'language' => ['type' => 'string'],
                'roles' => ['type' => 'array', 'items' => ['type' => 'string']],
            ])
            ->assertResponseMatches([
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'language' => \sprintf('/api/languages/%s', $user->getLanguage()->value),
                'roles' => $user->getRoles(),
            ])
        ;
    }

    #[Test]
    public function admin_get_collection(): void
    {
        /** @var User $admin */
        $admin = $this->getFactory()->admin()->create();

        $this->browser()->actingAs($admin)
            ->get($this->getBaseUrl())
            ->assertStatus(200)
            ->assertJsonCollectionSchemaOk([
                'id' => ['type' => 'integer'],
                'username' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'language' => ['type' => 'string'],
                'roles' => ['type' => 'array', 'items' => ['type' => 'string']],
            ], 1)
            ->assertCollectionResponseMatches([$admin], fn (User $user) => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'language' => \sprintf('/api/languages/%s', $user->getLanguage()->value),
                'roles' => $user->getRoles(),
            ])
        ;
    }

    #[Test]
    public function admin_edit(): void
    {
        /** @var User $user */
        $user = $this->getFactory()->with([
            'email' => 'oldemail@ambientrpg.dev',
            'language' => Language::AMERICAN_ENGLISH,
        ])->create();

        // Thanks to the new autorefresh we can't do nice stuff as before...
        $oldUsername = $user->getUsername();
        $oldRoles = $user->getRoles();

        $this->browser()->actingAs(UserFactory::new()->admin()->create())
            ->patch(\sprintf('%s/%s', $this->getBaseUrl(), $user->getId()), [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'json' => [
                    'username' => 'new_username',
                    'email' => 'newemail@ambientrpg.dev',
                    'language' => '/api/languages/fr_FR',
                    'roles' => ['ROLE_ADMIN'],
                ],
            ])
            ->assertStatus(200)
            ->assertJsonItemSchemaOk([
                'id' => ['type' => 'integer'],
                'username' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'language' => ['type' => 'string'],
                'roles' => ['type' => 'array', 'items' => ['type' => 'string']],
            ])
            ->assertResponseMatches([
                'id' => $user->getId(),
                'username' => $oldUsername,
                'email' => 'newemail@ambientrpg.dev',
                'language' => \sprintf('/api/languages/%s', Language::FRENCH->value),
                'roles' => $oldRoles,
            ])
        ;
    }

    #[Test]
    public function admin_edit_duplicate_email(): void
    {
        $this->getFactory()->with(['email' => 'email@ambientrpg.dev'])->create();

        /** @var User $user */
        $user = $this->getFactory()->with([
            'email' => 'some.email@ambientrpg.dev',
            'language' => Language::AMERICAN_ENGLISH,
        ])->create();

        $user->setEmail('email@ambientrpg.dev');

        $this->browser()->actingAs(UserFactory::new()->admin()->create())
            ->patch(\sprintf('%s/%s', $this->getBaseUrl(), $user->getId()), [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'json' => ['email' => 'email@ambientrpg.dev'],
            ])
            ->assertStatus(422)
            ->assertValidationError(['Email already taken'])
        ;
    }

    public function getBaseUrl(): string
    {
        return '/api/users';
    }

    /**
     * @return UserFactory
     */
    public function getFactory(): PersistentObjectFactory
    {
        return UserFactory::new();
    }
}
