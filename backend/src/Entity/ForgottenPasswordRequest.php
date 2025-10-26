<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\ApiConfig\ForgottenPasswordRequestApiConfig;
use App\Model\ForgottenPasswordRequest as ModelForgottenPasswordRequest;
use App\Model\SetPassword;
use App\Repository\ForgottenPasswordRequestRepository;
use App\State\Processor\ForgottenPasswordRequestProcessor;
use App\State\Processor\RequestForgottenPasswordRequestProcessor;
use App\State\Provider\ForgottenPasswordRequestProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/forgotten_password_requests/{code}',
            uriVariables: ['code'],
            normalizationContext: [AbstractNormalizer::GROUPS => [ForgottenPasswordRequestApiConfig::GET]],
            provider: ForgottenPasswordRequestProvider::class,
        ),
        new Post(
            uriTemplate: '/forgotten_password_requests/{code}',
            uriVariables: ['code'],
            input: SetPassword::class,
            processor: ForgottenPasswordRequestProcessor::class,
        ),
        new Post(
            input: ModelForgottenPasswordRequest::class,
            processor: RequestForgottenPasswordRequestProcessor::class,
        ),
    ],
)]
#[ORM\Entity(repositoryClass: ForgottenPasswordRequestRepository::class)]
class ForgottenPasswordRequest
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ApiProperty(readable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $requestedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $expiresAt;

    // Null if generated from the CLI
    #[ORM\Column(type: Types::STRING, length: 512, nullable: true)]
    private ?string $requestedFromIp = null;

    #[ORM\Column(type: Types::STRING, length: 64)]
    #[ApiProperty(identifier: true)]
    #[Groups([
        ForgottenPasswordRequestApiConfig::GET,
    ])]
    private string $code;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRequestedAt(): \DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeImmutable $requestedAt): static
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getRequestedFromIp(): ?string
    {
        return $this->requestedFromIp;
    }

    public function setRequestedFromIp(?string $requestedFromIp): static
    {
        $this->requestedFromIp = $requestedFromIp;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }
}
