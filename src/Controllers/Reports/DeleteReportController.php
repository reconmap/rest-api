<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ReportRepository;

class DeleteReportController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $id = (int)$args['id'];

        $repository = new ReportRepository($this->db);
        $report = $repository->findById($id);
        $success = $repository->deleteById($id);

        $files = glob(sprintf(RECONMAP_APP_DIR . "/data/reports/report-%d.*", $id));
        foreach ($files as $filename) {
            if (unlink($filename) === false) {
                $this->logger->warning("Unable to delete report file '$filename'");
            }
        }

        return ['success' => $success];
    }
}
