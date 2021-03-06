<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use Reconmap\Repositories\CommandRepository;

class CommandsImporter
{
    public function __construct(private CommandRepository $repository)
    {
    }

    public function import(int $userId, array $commands): array
    {
        $response = [
            'count' => 0,
            'errors' => [],
        ];

        foreach ($commands as $jsonCommand) {
            $jsonCommand->creator_uid = $userId;
            $this->repository->insert($jsonCommand);

            $response['count']++;
        }

        return $response;
    }
}
