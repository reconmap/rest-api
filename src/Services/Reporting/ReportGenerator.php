<?php declare(strict_types=1);

namespace Reconmap\Services\Reporting;

use Reconmap\Services\TemplateEngine;

class ReportGenerator
{
    public function __construct(
        private ReportDataCollector $reportDataCollector,
        private TemplateEngine      $templateEngine)
    {
    }

    public function generate(int $projectId): array
    {
        $vars = $this->reportDataCollector->collectForProject($projectId);

        return [
            'body' => $this->templateEngine->render('reports/body', $vars)
        ];
    }
}
