<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ReportConfigurationRepository;

class GetReportConfigurationController extends Controller
{
    public function __construct(private ReportConfigurationRepository $configurationRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): object
    {
        $projectId = (int)$args['projectId'];

        return $this->configurationRepository->findByProjectId($projectId);
    }
}
