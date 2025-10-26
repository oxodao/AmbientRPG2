<?php

namespace App\Command\Mercure;

use App\ApiConfig\UserApiConfig;
use App\Repository\UserRepository;
use App\Service\Mercure;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mercure:submit-test-event', description: 'Send a test event for a given user')]
readonly class SubmitTestEventCommand
{
    public function __construct(
        private UserRepository $userRepository,
        private Mercure $mercure,
    ) {
    }

    public function __invoke(
        #[Argument(description: 'The username of the user')]
        string $username,
        OutputInterface $output,
    ): int {
        $user = $this->userRepository->findOneByUsername($username);
        if (!$user) {
            $output->writeln('<error>User not found</error>');

            return Command::FAILURE;
        }

        $this->mercure->submitToUsers(
            '/messages',
            $user,
            [UserApiConfig::GET],
            [$user->getId()],
        );

        return Command::SUCCESS;
    }
}
