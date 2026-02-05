<?php

namespace Dashboard\Handler;

use Dashboard\Service\Database\DatabaseStatsServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Repository\DatabaseRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ShowDatabasesHandler implements RequestHandlerInterface
{
    private int $connectTimeoutSeconds = 2;

    public function __construct(
        private TemplateRendererInterface $template,
        private DatabaseStatsServiceInterface $service
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     * @todo needs refactoring, database stats in a service
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $results = $this->service->execute();

        $content = $this->template->render('dashboard::databases', [
            'results' => $results->getElements(),
        ]);

        return new HtmlResponse($this->template->render('layout::dashboard', [
            'content' => $content,
            'data'    => $results,
        ]));
    }

    private function fetchDatabaseSizeMb(Connection $conn): ?float
    {
        $qb = $conn->createQueryBuilder();

        $qb->select('ROUND(SUM(t.data_length + t.index_length) / 1024 / 1024, 2) AS size_mb')
            ->from('information_schema.tables', 't')
            ->where('t.table_schema = DATABASE()');

        $value = $qb->executeQuery()->fetchOne();

        return $value !== false && $value !== null ? (float) $value : 0;
    }


    private function connectToDatabase(string $dbName): Connection
    {
        $params = $this->config;
        $params['dbname'] = $dbName;

        $params['driverOptions'] = ($params['driverOptions'] ?? []) + [
                \PDO::ATTR_TIMEOUT => $this->connectTimeoutSeconds,
            ];

        // Ensure we have a driver key (should come from config)
        $params['driver'] ??= 'pdo_mysql';
        unset($params['driverClass']); // avoid mixing styles

        return DriverManager::getConnection($params);
    }


    /**
     * @throws Exception
     */
    private function fetchDatabaseCreatedAt(Connection $conn): ?\DateTimeImmutable
    {
        $qb = $conn->createQueryBuilder();

        $qb->select('MIN(t.CREATE_TIME) AS created_at')
            ->from('information_schema.tables', 't')
            ->where('t.table_schema = DATABASE()');

        $value = $qb->executeQuery()->fetchOne();

        return $value ? new \DateTimeImmutable($value) : null;
    }
}
