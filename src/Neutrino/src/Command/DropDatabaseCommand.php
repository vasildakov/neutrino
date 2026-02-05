<?php

declare(strict_types=1);

namespace Neutrino\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DropDatabaseCommand extends Command
{
    public function __construct(private Connection $adminConnection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('neutrino:database:drop')
            ->setDescription('Drop the tenant database')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the database drop')
            ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'Database name to drop')
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Database user to drop');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('force')) {
            $output->writeln('<error>You must use --force option to drop the database</error>');
            return Command::FAILURE;
        }

        $dbName = $input->getOption('database');
        $dbUser = $input->getOption('user');

        if (!$dbName || !$dbUser) {
            $output->writeln('<error>Both --database and --user options are required</error>');
            return Command::FAILURE;
        }

        try {
            $this->adminConnection->executeStatement("DROP DATABASE IF EXISTS `{$dbName}`");
            $this->adminConnection->executeStatement("DROP USER IF EXISTS '{$dbUser}'@'%'");
            $this->adminConnection->executeStatement('FLUSH PRIVILEGES');

            $output->writeln('<info>Database and user dropped successfully</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
