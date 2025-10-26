<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiConfig\CampaignApiConfig;
use App\ApiConfig\EnumApiConfig;
use App\Enum\Game;
use App\Repository\CampaignRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Note:
 * We are using a custom Doctrine Extension to let users only see their own campaigns
 * This has the advantage of not having to add security to each query manually for subresources (I hope, not tried it yet)
 *
 * BUT this means that those endpoints will 404 instead of 403
 */

#[ApiResource(
    operations: [
        new Get(security: 'is_granted("ROLE_ADMIN") or object.getOwner() == user'),
        new GetCollection(
            order: ['id' => 'DESC'],
            normalizationContext: [AbstractNormalizer::GROUPS => [CampaignApiConfig::GET_COLLECTION, EnumApiConfig::GET]],
            security: 'is_granted("ROLE_DM")',
        ),
        new Post(
            denormalizationContext: [AbstractNormalizer::GROUPS => [CampaignApiConfig::POST]],
            security: 'is_granted("ROLE_DM")',
        ),
        new Patch(
            denormalizationContext: [AbstractNormalizer::GROUPS => [CampaignApiConfig::PATCH]],
            security: 'is_granted("ROLE_ADMIN") or object.getOwner() == user',
        ),
        new Delete(security: 'is_granted("ROLE_ADMIN") or object.getOwner() == user'),
    ],
    normalizationContext: [AbstractNormalizer::GROUPS => [CampaignApiConfig::GET, EnumApiConfig::GET]],
)]
#[ORM\Entity(repositoryClass: CampaignRepository::class)]
class Campaign
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups([
        ...CampaignApiConfig::_VIEW,
    ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 128, unique: true)]
    #[Assert\NotBlank(message: 'not_blank')]
    #[Assert\Length(min: 3, max: 128)]
    #[Groups([
        ...CampaignApiConfig::_VIEW,
        CampaignApiConfig::POST,
        CampaignApiConfig::PATCH,
    ])]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 128, enumType: Game::class)]
    #[Assert\NotBlank(message: 'not_blank')]
    #[Groups([
        ...CampaignApiConfig::_VIEW,
        CampaignApiConfig::POST,
        CampaignApiConfig::PATCH,
    ])]
    private Game $game;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'campaigns')]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;
        $owner->addCampaign($this);

        return $this;
    }
}
