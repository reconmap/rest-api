<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use Reconmap\Models\Document;
use Reconmap\Repositories\DocumentRepository;

class DocumentsImporter implements Importable
{
    public function __construct(private DocumentRepository $repository)
    {
    }

    /**
     * @param int $userId
     * @param array<Document> $documents
     * @return array
     */
    public function import(int $userId, array $documents): array
    {
        $response = [
            'count' => 0,
            'errors' => [],
        ];

        foreach ($documents as $jsonDoc) {
            try {
                $jsonDoc->user_id = $userId;
                $this->repository->insert($jsonDoc);

                $response['count']++;
            } catch (\Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        }

        return $response;
    }
}
