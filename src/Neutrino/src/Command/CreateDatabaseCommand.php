<?php

declare(strict_types=1);
/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Neutrino\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDatabaseCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this->setName('neutrino:database:create')
            ->setDescription('Create tenant application database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Tenant database and user details
            $dbName     = 'tenant_bar';
            $dbUser     = 'tenant_bar';
            $dbPassword = 'bar';

            $this->connection->executeStatement(
                sql: "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );

            $this->connection->executeStatement(
                sql: "CREATE USER IF NOT EXISTS '{$dbUser}'@'%' IDENTIFIED BY '{$dbPassword}'"
            );

            $this->connection->executeStatement(
                sql: "GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'%'"
            );

            $this->connection->executeStatement(sql: 'FLUSH PRIVILEGES');

            $output->writeln('<info>Database and user created successfully</info>');
            $status = Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('<error>Failed: ' . $e->getMessage() . '</error>');
            $status = Command::FAILURE;
        }

        return $status;
    }
}
