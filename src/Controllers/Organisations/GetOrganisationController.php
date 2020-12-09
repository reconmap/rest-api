<?php declare(strict_types=1);

namespace Reconmap\Controllers\Organisations;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\OrganisationRepository;

class GetOrganisationController extends Controller
{
    public function __invoke(ServerRequestInterface $request): object
    {
        $repository = new OrganisationRepository($this->db);
        return $repository->findRootOrganisation();
    }
}
