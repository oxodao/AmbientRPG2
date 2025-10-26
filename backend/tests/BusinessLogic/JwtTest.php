<?php

namespace App\Tests\BusinessLogic;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\UserFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Zenstruck\Foundry\Test\Factories;

class JwtTest extends ApiTestCase
{
    use Factories;

    /**
     * This stuff is radioactive
     * For some reason, sometime the payload is null.
     *
     * This method is fixed-by-gpt so keep it under a lock if it starts to behave weirdly
     */
    #[Test]
    public function ensure_mercure_claims_in_jwt(): void
    {
        /** @var JWTTokenManagerInterface $manager */
        $manager = self::getContainer()->get(JWTTokenManagerInterface::class);

        $user = UserFactory::new()->create();

        $jwt = $manager->create($user);

        $this->assertNotEmpty($jwt, 'JWT token should not be empty');

        $parts = \explode('.', $jwt);
        $this->assertCount(3, $parts, 'JWT should have 3 parts (header.payload.signature)');

        $payloadEncoded = $parts[1];
        $this->assertNotEmpty($payloadEncoded, 'JWT payload part should not be empty');

        // JWT uses base64 URL-safe encoding, convert it to standard base64
        $payloadBase64 = \strtr($payloadEncoded, '-_', '+/');
        // Add padding if needed
        $payloadBase64 = \str_pad($payloadBase64, \strlen($payloadBase64) % 4, '=', \STR_PAD_RIGHT);

        $payloadDecoded = \base64_decode($payloadBase64);
        $this->assertNotFalse($payloadDecoded, 'JWT payload should be valid base64');

        $payload = \json_decode($payloadDecoded, true);
        $this->assertIsArray($payload, \sprintf(
            'JWT payload should be valid JSON. Decoded: %s, JSON error: %s',
            $payloadDecoded,
            \json_last_error_msg(),
        ));

        $this->assertArrayHasKey('mercure', $payload);

        $mercure = $payload['mercure'];

        if (\array_key_exists('publish', $mercure)) {
            $this->assertCount(0, $mercure['publish']);
        }

        $this->assertArrayHasKey('subscribe', $mercure);

        $expectedTopics = [
            \sprintf('/users/%d{?topic}', $user->getId()),
        ];

        $this->assertCount(\count($expectedTopics), $mercure['subscribe']);

        foreach ($expectedTopics as $expectedTopic) {
            $this->assertContains($expectedTopic, $mercure['subscribe']);
        }
    }
}
