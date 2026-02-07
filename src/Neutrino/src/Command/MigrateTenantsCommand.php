<?php

namespace Neutrino\Command;

use Neutrino\Tenant\TenantMigrationsRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateTenantsCommand extends Command
{
    public function __construct(
        private readonly TenantMigrationsRunner $runner,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $results = $this->runner->migrateAll();

        foreach ($results as $dbName => $status) {
            if ($status === 'ok') {
                $output->writeln(sprintf('<info>[%s]</info> OK', $dbName));
                continue;
            }

            $output->writeln(sprintf('<error>[%s]</error> %s', $dbName, $status));
        }

        // If any failed, return failure (useful for CI/tests)
        foreach ($results as $status) {
            if ($status !== 'ok') {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}