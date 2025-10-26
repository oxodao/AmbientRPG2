<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\ApiConfig\UserApiConfig;
use App\Enum\Language;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oxodao\QneOAuthBundle\Behavior\Impl\OAuthUserTrait;
use Oxodao\QneOAuthBundle\Behavior\OAuthUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: [AbstractNormalizer::GROUPS => [UserApiConfig::GET]],
            security: 'is_granted("ROLE_ADMIN") or object == user',
        ),
        new GetCollection(
            normalizationContext: [AbstractNormalizer::GROUPS => [UserApiConfig::GET_COLLECTION]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Patch(
            normalizationContext: [AbstractNormalizer::GROUPS => [UserApiConfig::GET]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [UserApiConfig::PATCH]],
            security: 'is_granted("ROLE_ADMIN") or object == user',
        ),
    ],
)]
#[UniqueEntity(fields: ['username'], message: 'Username already taken')]
#[UniqueEntity(fields: ['email'], message: 'Email already taken')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_users')]
class User implements UserInterface, OAuthUserInterface
{
    use OAuthUserTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups([
        ...UserApiConfig::_VIEW,
    ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 32, unique: true)]
    #[Assert\NotBlank(message: 'not_blank')]
    #[Assert\Length(min: 3, max: 32)]
    #[Groups([
        ...UserApiConfig::_VIEW,
    ])]
    private string $username;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank(message: 'not_blank')]
    #[Assert\Email(message: 'invalid_email')]
    #[Groups([
        ...UserApiConfig::_VIEW,
        UserApiConfig::PATCH,
    ])]
    private string $email;

    #[ORM\Column(
        type: Types::STRING,
        length: 255,
        enumType: Language::class,
        options: ['default' => Language::AMERICAN_ENGLISH->value],
    )]
    #[Assert\NotBlank(message: 'not_blank')]
    #[Groups([
        ...UserApiConfig::_VIEW,
        UserApiConfig::PATCH,
    ])]
    private Language $language;

    /** @var string[] $roles */
    #[ORM\Column(type: Types::JSON)]
    #[Groups([
        ...UserApiConfig::_VIEW,
    ])]
    private array $roles = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): static
    {
        $this->language = $language;

        return $this;
    }

    /**
     * While it is often seen in Symfony apps that this method adds at least ROLE_USER by default,
     * we should not do this, having an account doesn't automatically say that you can use the app
     * e.g. OAuth users might have an account for other app but not be allowed this one.
     *
     * Password-registered user should have the ROLE_USER assigned during registration.
     *
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        // phpstan craps itself if we don't do this
        // gneugneugneugneu "non-empty-string" but property is "string" idgaf there are validation rules
        \assert(isset($this->username) && '' !== $this->username);

        return $this->username;
    }
}
