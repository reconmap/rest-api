<?php declare(strict_types=1);

namespace Reconmap\Controllers\Organisations;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Organisation;
use Reconmap\Repositories\OrganisationRepository;

class UpdateOrganisationController extends Controller
{
    public function __invoke(ServerRequestInterface $request): array
    {
        /** @var Organisation $organisation */
        $organisation = $this->getJsonBodyDecoded($request);

        $repository = new OrganisationRepository($this->db);
        $success = $repository->updateRootOrganisation($organisation);

        return ['success' => $success];
    }
}
