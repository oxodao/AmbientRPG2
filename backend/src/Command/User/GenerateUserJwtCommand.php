<?php

namespace App\Command\User;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:user:generate-jwt', description: 'Generate a JWT for a given user')]
readonly class GenerateUserJwtCommand
{
    public function __construct(
        private UserRepository $userRepository,
        private JWTTokenManagerInterface $jwt,
    ) {
    }

    public function __invoke(
        #[Argument(description: 'The username of the user')]
        string $username,
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $user = $this->userRepository->findOneByUsername($username);
        if (!$user) {
            $io->error(\sprintf('User "%s" not found.', $username));

            return Command::FAILURE;
        }

        $token = $this->jwt->create($user);

        $io->success(\sprintf('JWT for user "%s" generated:', $username));

        echo $token . PHP_EOL;

        return Command::SUCCESS;
    }
}
