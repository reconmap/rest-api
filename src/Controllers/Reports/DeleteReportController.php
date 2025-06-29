<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

#[OpenApi\Delete(path: "/reports/{reportId}", description: "Deletes report with the given id", security: ["bearerAuth"], tags: ["Reports"], parameters: [new InPathIdParameter("reportId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteReportController extends Controller
{
    public function __construct(private readonly AttachmentFilePath   $attachmentFilePathService,
                                private readonly ReportRepository     $reportRepository,
                                private readonly AttachmentRepository $attachmentRepository,
                                private readonly Filesystem           $filesystem)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $reportId = (int)$args['reportId'];

        $success = $this->reportRepository->deleteById($reportId);

        $attachments = $this->attachmentRepository->findByParentId('report', $reportId);
        foreach ($attachments as $attachment) {
            $filePath = $this->attachmentFilePathService->generateFilePath($attachment['file_name']);

            try {
                $this->filesystem->remove($filePath);
            } catch (IOException $ioe) {
                $this->logger->warning($ioe->getMessage());
            }
        }

        return $success ? $this->createNoContentResponse() : $this->createInternalServerErrorResponse();
    }
}
