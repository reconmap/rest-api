<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ReportRepository;

class GetReportTemplatesController extends Controller
{
    public function __construct(private readonly ReportRepository $reportRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return $this->reportRepository->findTemplates();
    }
}
