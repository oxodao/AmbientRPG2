<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\ForgottenPasswordRequest;
use App\Repository\ForgottenPasswordRequestRepository;

/**
 * @implements ProviderInterface<ForgottenPasswordRequest>
 */
readonly class ForgottenPasswordRequestProvider implements ProviderInterface
{
    public function __construct(
        private ForgottenPasswordRequestRepository $forgottenPasswordRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->forgottenPasswordRepository->findOneByCode($uriVariables['code']);
    }
}
