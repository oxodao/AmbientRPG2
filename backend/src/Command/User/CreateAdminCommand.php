<?php

namespace App\Command\User;

use App\Entity\User;
use App\Enum\Language;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand('app:users:create-admin')]
readonly class CreateAdminCommand
{
    public function __construct(
        private EntityManagerInterface $emi,
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function __invoke(
        #[Argument('username')]
        string $username,
        #[Argument('email')]
        string $email,
        SymfonyStyle $io,
    ): int {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setLanguage(Language::AMERICAN_ENGLISH);

        $password = \bin2hex(\random_bytes(12));
        $user->setPassword($this->hasher->hashPassword($user, $password));

        $this->emi->persist($user);
        $this->emi->flush();

        $io->success("User {$username} created with password {$password}");

        return Command::SUCCESS;
    }
}
