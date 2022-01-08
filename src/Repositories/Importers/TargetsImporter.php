<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use Reconmap\Models\Target;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Services\ObjectCaster;

class TargetsImporter implements Importable
{
    public function __construct(private TargetRepository $repository)
    {
    }

    /**
     * @param int $userId
     * @param array<Target> $targets
     * @return array
     */
    public function import(int $userId, array $targets): array
    {
        $response = [
            'count' => 0,
            'errors' => [],
        ];

        foreach ($targets as $jsonTarget) {
            try {
                /** @var Target $jsonTarget */
                $jsonTarget = ObjectCaster::cast(new Target(), $jsonTarget);
                $this->repository->insert($jsonTarget);

                $response['count']++;
            } catch (\Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        }

        return $response;
    }
}
