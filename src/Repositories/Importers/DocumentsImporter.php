<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use Reconmap\Repositories\DocumentRepository;

class DocumentsImporter implements Importable
{
    public function __construct(private DocumentRepository $repository)
    {
    }

    public function import(int $userId, array $documents): array
    {
        $response = [
            'count' => 0,
            'errors' => [],
        ];

        foreach ($documents as $jsonDoc) {
            try {
                $this->repository->insert($userId, $jsonDoc);

                $response['count']++;
            } catch (\Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        }

        return $response;
    }
}
