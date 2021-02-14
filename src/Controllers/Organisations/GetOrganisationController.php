<?php declare(strict_types=1);

namespace Reconmap\Controllers\Organisations;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\OrganisationRepository;

class GetOrganisationController extends Controller
{
    private OrganisationRepository $repository;

    public function __construct(OrganisationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(ServerRequestInterface $request): object
    {
        return $this->repository->findRootOrganisation();
    }
}
