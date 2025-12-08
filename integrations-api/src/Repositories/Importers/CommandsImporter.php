<?php

declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use Reconmap\Models\Command;
use Reconmap\Repositories\CommandRepository;

class CommandsImporter implements Importable
{
    public function __construct(private CommandRepository $repository) {}

    /**
     * @param int $userId
     * @param array<Command> $commands
     * @return array
     */
    public function import(int $userId, array $commands): array
    {
        $response = [
            'count' => 0,
            'errors' => [],
        ];

        foreach ($commands as $jsonCommand) {
            $jsonCommand->created_by_uid = $userId;
            $this->repository->insert($jsonCommand);

            $response['count']++;
        }

        return $response;
    }
}
