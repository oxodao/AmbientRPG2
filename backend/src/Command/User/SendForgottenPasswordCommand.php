<?php

namespace App\Command\User;

use App\Repository\UserRepository;
use App\Service\UserManager;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:user:send-forgotten-password', description: 'Send the user a forgotten password email')]
readonly class SendForgottenPasswordCommand
{
    public function __construct(
        private UserRepository $userRepository,
        private UserManager $manager,
    ) {
    }

    public function __invoke(
        #[Argument('username')] string $username,
        SymfonyStyle $io,
    ): int {
        $user = $this->userRepository->findOneByUsername($username);
        if (!$user) {
            $io->error(sprintf('User with username "%s" not found.', $username));

            return Command::FAILURE;
        }

        $this->manager->generateAndSendPasswordForgotten($user);

        return Command::SUCCESS;
    }
}
