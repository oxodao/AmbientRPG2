<?php

namespace App\Service;

use App\Entity\ForgottenPasswordRequest;
use App\Entity\User;
use App\Message\ForgottenPasswordNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class UserManager
{
    public function __construct(
        private MessageBusInterface $bus,
        private EntityManagerInterface $emi,
        private RequestStack $requestStack,
    ) {
    }

    public function generateAndSendPasswordForgotten(User $user): void
    {
        $ip = null;
        $request = $this->requestStack->getMainRequest();
        if ($request) {
            $ip = $request->getClientIp();
        }

        $pwdRequest = new ForgottenPasswordRequest();
        $pwdRequest->setUser($user);
        $pwdRequest->setRequestedAt(new \DateTimeImmutable());
        $pwdRequest->setExpiresAt(new \DateTimeImmutable()->add(new \DateInterval('PT24H')));
        $pwdRequest->setCode(\bin2hex(\random_bytes(32)));
        $pwdRequest->setRequestedFromIp($ip);

        $this->emi->persist($pwdRequest);
        $this->emi->flush();

        $this->bus->dispatch(new ForgottenPasswordNotification($user, $pwdRequest->getCode()));
    }
}
