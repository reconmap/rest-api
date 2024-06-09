<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use Reconmap\DomainObjects\Document;
use Reconmap\Repositories\DocumentRepository;

readonly class DocumentsImporter implements Importable
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
                $document = Document::fromObject($jsonDoc);
                $document->user_id = $userId;
                $this->repository->insert($document);

                $response['count']++;
            } catch (\Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        }

        return $response;
    }
}
