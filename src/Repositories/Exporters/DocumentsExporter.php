<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\DocumentRepository;

class DocumentsExporter implements Exportable
{
    public function __construct(private DocumentRepository $repository)
    {
    }

    public function export(): array
    {
        return $this->repository->findAll();
    }
}
