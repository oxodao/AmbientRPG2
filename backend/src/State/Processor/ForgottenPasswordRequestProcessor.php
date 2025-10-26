<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiConfig\ForgottenPasswordRequestApiConfig;
use App\Message\PasswordUpdatedNotification;
use App\Model\SetPassword;
use App\Repository\ForgottenPasswordRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<SetPassword, Response>
 */
readonly class ForgottenPasswordRequestProcessor implements ProcessorInterface
{
    public function __construct(
        private ForgottenPasswordRequestRepository $repository,
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        $rq = $this->repository->findOneByCode($uriVariables['code']);
        if (!$rq) {
            throw new NotFoundHttpException();
        }

        $violations = $this->validator->validate($data, groups: [ForgottenPasswordRequestApiConfig::VALIDATE_FORGOTTEN]);
        if (\count($violations) > 0) {
            throw new ValidationException($violations);
        }

        $newPassword = $this->hasher->hashPassword($rq->getUser(), $data->newPassword);

        $rq->getUser()->setPassword($newPassword);
        $this->entityManager->persist($rq->getUser());
        $this->entityManager->remove($rq);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new PasswordUpdatedNotification($rq->getUser()));

        return new Response(status: 200);
    }
}
