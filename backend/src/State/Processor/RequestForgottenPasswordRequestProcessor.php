<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Model\ForgottenPasswordRequest;
use App\Repository\UserRepository;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * @implements ProcessorInterface<ForgottenPasswordRequest, Response>
 */
readonly class RequestForgottenPasswordRequestProcessor implements ProcessorInterface
{
    public function __construct(
        private UserManager $manager,
        private UserRepository $repository,
        private RequestStack $requestStack,
        private RateLimiterFactory $passwordResetLimiter,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request) {
            return new Response(status: 200);
        }

        $ip = $request->getClientIp();

        if ($ip) {
            $limiter = $this->passwordResetLimiter->create($ip);

            $limit = $limiter->consume();
            if (!$limit->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $email = $data->email;
        $user = $this->repository->findOneByEmail($email);

        // This is stupid because we can find out if a user has an account with the register form
        // but meh, everyone's doing it so lets do it anyway
        if (!$user) {
            return new Response(status: 200);
        }

        $this->manager->generateAndSendPasswordForgotten($user);

        return new Response(status: 200);
    }
}
