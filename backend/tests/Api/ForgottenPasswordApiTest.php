<?php

namespace App\Tests\Api;

use App\Entity\ForgottenPasswordRequest;
use App\Enum\Language;
use App\Factory\ForgottenPasswordRequestFactory;
use App\Factory\UserFactory;
use App\Message\ForgottenPasswordNotification;
use App\Message\PasswordUpdatedNotification;
use App\Tests\ApiTestCase;
use App\Tests\Helper\Trait\NoEndpoint\DeleteNoEndpointTrait;
use App\Tests\Helper\Trait\NoEndpoint\GetCollectionNoEndpointTrait;
use App\Tests\Helper\Trait\NoEndpoint\PatchNoEndpointTrait;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Zenstruck\Assert;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Messenger\Test\InteractsWithMessenger;
use Zenstruck\Messenger\Test\Transport\TransportEnvelopeCollection;

/**
 * @extends ApiTestCase<ForgottenPasswordRequest>
 */
class ForgottenPasswordApiTest extends ApiTestCase
{
    use InteractsWithMessenger;

    /* We do not have an endpoint for get collection */
    /** @use GetCollectionNoEndpointTrait<ForgottenPasswordRequest> */
    use GetCollectionNoEndpointTrait;

    /* We do not have an endpoint for patch */
    /** @use PatchNoEndpointTrait<ForgottenPasswordRequest> */
    use PatchNoEndpointTrait;

    /* We do not have an endpoint for delete */
    /** @use DeleteNoEndpointTrait<ForgottenPasswordRequest> */
    use DeleteNoEndpointTrait;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var FilesystemAdapter $rateLimiter */
        $rateLimiter = self::getContainer()->get('cache.rate_limiter');
        $rateLimiter->clear();
    }

    #[Test]
    public function forgottenpasswordrequest_get(): void
    {
        $code = $this->getFactory()->create();

        self::browser()
            ->get(\sprintf('%s/%s', $this->getBaseUrl(), $code->getCode()))
            ->assertStatus(200)
            ->assertJsonItemSchemaOk([
                'code' => ['type' => 'string'],
            ])
            ->assertResponseMatches([
                'code' => $code->getCode(),
            ])
        ;
    }

    #[Test]
    public function forgottenpasswordrequest_get_expired(): void
    {
        $code = $this->getFactory()->expired()->create();

        self::browser()
            ->get(\sprintf('%s/%s', $this->getBaseUrl(), $code->getCode()))
            ->assertStatus(404)
        ;
    }

    #[Test]
    public function forgottenpasswordrequest_get_notfound(): void
    {
        self::browser()
            ->get(\sprintf('%s/%s', $this->getBaseUrl(), 'aazzeee'))
            ->assertStatus(404)
        ;
    }

    #[Test]
    public function forgottenpasswordrequest_update(): void
    {
        $code = $this->getFactory()->create();

        $oldPassword = $code->getUser()->getPassword();

        self::browser()
            ->post(\sprintf('%s/%s', $this->getBaseUrl(), $code->getCode()), [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'newPassword' => 'Some_Strong_New_Passw0rd',
                ],
            ])
            ->assertStatus(200)
        ;

        Assert::that($code->getUser()->getPassword())->isNotEqualTo($oldPassword);

        $this->getEmailQueue()->assertCount(1);
        $this->getEmailQueue()->assertContains(PasswordUpdatedNotification::class);

        /** @var PasswordUpdatedNotification $message */
        $message = $this->getEmailQueue()->messages(PasswordUpdatedNotification::class)[0];
        Assert::that($message->getUsername())->equals($code->getUser()->getUsername());
        Assert::that($message->getEmail())->equals($code->getUser()->getEmail());
        Assert::that($message->getLanguage())->equals($code->getUser()->getLanguage()->value);
    }

    #[Test]
    public function forgottenpasswordrequest_update_expired(): void
    {
        $code = $this->getFactory()->expired()->create();

        $oldPassword = $code->getUser()->getPassword();

        self::browser()
            ->post(\sprintf('%s/%s', $this->getBaseUrl(), $code->getCode()), [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'newPassword' => 'newPassword123!',
                ],
            ])
            ->assertStatus(404)
        ;

        Assert::that($code->getUser()->getPassword())->equals($oldPassword);

        $this->getEmailQueue()->assertEmpty();
    }

    #[Test]
    public function forgottenpasswordrequest_update_notfound(): void
    {
        self::browser()
            ->post(\sprintf('%s/%s', $this->getBaseUrl(), 'aazzeee'), [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'newPassword' => 'newPassword123!',
                ],
            ])
            ->assertStatus(404)
        ;

        $this->getEmailQueue()->assertEmpty();
    }

    #[Test]
    public function forgottenpasswordrequest_post(): void
    {
        $user = UserFactory::new()->withLanguage(Language::FRENCH)->create();

        $browser = self::browser();
        $browser
            ->post($this->getBaseUrl(), [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'email' => $user->getEmail(),
                ],
            ])
            ->assertStatus(200)
        ;

        Assert::that($browser->content())->isEmpty();

        $this->getEmailQueue()->assertCount(1);
        $this->getEmailQueue()->assertContains(ForgottenPasswordNotification::class);

        /** @var ForgottenPasswordNotification $message */
        $message = $this->getEmailQueue()->messages(ForgottenPasswordNotification::class)[0];

        Assert::that($message->getUsername())->equals($user->getUsername());
        Assert::that($message->getLanguage())->equals('fr_FR');
        Assert::that($message->getEmail())->equals($user->getEmail());
        Assert::that($message->getCode())->isNotEmpty();
    }

    #[Test]
    public function forgottenpasswordrequest_ratelimit(): void
    {
        $user = UserFactory::new()->create();

        $browser = self::browser();

        for ($i = 0; $i < 5; ++$i) {
            $browser
                ->post($this->getBaseUrl(), [
                    'headers' => ['Content-Type' => 'application/json'],
                    'json' => [
                        'email' => $user->getEmail(),
                    ],
                ])
                ->assertStatus(200)
            ;

            Assert::that($browser->content())->isEmpty();
        }

        $this->getEmailQueue()->assertCount(5);

        $browser
            ->post($this->getBaseUrl(), [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'email' => $user->getEmail(),
                ],
            ])
            ->assertStatus(429)
        ;

        $this->getEmailQueue()->assertCount(5);
    }

    #[Test]
    public function forgottenpasswordrequest_post_notfound(): void
    {
        $browser = self::browser();
        $browser
            ->post($this->getBaseUrl(), [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'email' => 'some.random@email.dev',
                ],
            ])
            ->assertStatus(200)
        ;

        Assert::that($browser->content())->isEmpty();

        $this->getEmailQueue()->assertEmpty();
    }

    public function getBaseUrl(): string
    {
        return '/api/forgotten_password_requests';
    }

    /**
     * @return ForgottenPasswordRequestFactory
     */
    public function getFactory(): PersistentObjectFactory
    {
        return ForgottenPasswordRequestFactory::new();
    }

    public function getEmailQueue(): TransportEnvelopeCollection
    {
        return $this->transport('emails')->queue();
    }
}
