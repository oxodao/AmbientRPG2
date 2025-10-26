<?php

namespace App\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * This context builder lets you add serialization groups based on user roles.
 */
#[AsDecorator(decorates: 'api_platform.serializer.context_builder')]
class UserPermissionsContextBuilder implements SerializerContextBuilderInterface
{
    private const array GROUPS = ['ROLE_ADMIN' => 'admin'];

    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly Security $security,
    ) {
    }

    /**
     * @param array<mixed>|null $extractedAttributes
     *
     * @return array<mixed>
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $ctx = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (isset($ctx[AbstractNormalizer::GROUPS])) {
            if (!\is_array($ctx[AbstractNormalizer::GROUPS])) {
                $ctx[AbstractNormalizer::GROUPS] = [$ctx[AbstractNormalizer::GROUPS]];
            }

            $user = $this->security->getUser();
            $newGroups = [...$ctx[AbstractNormalizer::GROUPS]];

            if (!$user instanceof User) {
                return $ctx;
            }

            foreach ($ctx[AbstractNormalizer::GROUPS] as $group) {
                if (!\str_starts_with($group, 'api:')) {
                    continue;
                }

                foreach (self::GROUPS as $role => $roleGroup) {
                    if ($this->security->isGranted($role)) {
                        $newGroups[] = 'api:' . $roleGroup . \substr($group, 3);
                    }
                }
            }

            $ctx[AbstractNormalizer::GROUPS] = \array_unique($newGroups);
        }

        return $ctx;
    }
}
