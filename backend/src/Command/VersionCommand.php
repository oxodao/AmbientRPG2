<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'app:version', description: 'Get the current version of my_project')]
readonly class VersionCommand
{
    public function __construct(
        #[Autowire(param: 'APP_VERSION')]
        private string $version,
        #[Autowire(param: 'APP_COMMIT')]
        private string $commit,
    ) {
    }

    public function __invoke(OutputInterface $output): int
    {
        $output->writeln(\sprintf('MY_PROJECT %s (Commit %s)', $this->version, $this->commit));

        return Command::SUCCESS;
    }
}
