<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\Findable;

abstract class GetEntityController extends Controller
{
    public function __construct(private Findable $repository,
                                private string   $idParamName)
    {
    }

    /**
     * @throws NotFoundException
     */
    public function __invoke(ServerRequestInterface $request, array $args): array|object
    {
        $entityId = (int)$args[$this->idParamName];

        $entity = $this->repository->findById($entityId);
        if (is_null($entity)) {
            throw new NotFoundException("Entity #$entityId not found");
        }

        return $entity;
    }
}
