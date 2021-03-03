<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\AuditLogService;

class ExportDataController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $entities = explode(',', $request->getQueryParams()['entities']);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $entities);

        $fileName = 'reconmap-data-' . date('Ymd-His') . '.json';

        $exportables = [
            'clients' => [],
            'commands' => [],
            'projects' => [],
            'tasks' => [],
            'users' => [],
            'vulnerabilities' => []
        ];

        $body = new CallbackStream(function () use ($exportables, $entities) {
            $data = [];

            $outputStream = fopen('php://output', 'w');

            foreach ($exportables as $exportableKey => $exportable) {
                if (in_array($exportableKey, $entities)) {
                    $data[$exportableKey] = call_user_func([$this, 'export' . $exportableKey]);
                }
            }

            fwrite($outputStream, json_encode($data));
        });

        $response = new Response;
        return $response
            ->withBody($body)
            ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '";')
            ->withAddedHeader('Content-Type', 'application/json; charset=UTF-8');
    }

    private function exportClients(): array
    {
        $clientRepository = new ClientRepository($this->db);
        return $clientRepository->findAll();
    }

    private function exportCommands(): array
    {
        $commandRepository = new CommandRepository($this->db);
        return $commandRepository->findAll();
    }

    private function exportProjects(): array
    {
        $projectRepository = new ProjectRepository($this->db);
        return $projectRepository->findAll();
    }

    private function exportTasks(): array
    {
        $taskRepository = new TaskRepository($this->db);
        return $taskRepository->findAll(false, null);
    }

    private function exportUsers(): array
    {
        $userRepository = new UserRepository($this->db);
        return $userRepository->findAll();
    }

    private function exportVulnerabilities(): array
    {
        $vulnerabilityRepository = new VulnerabilityRepository($this->db);
        return $vulnerabilityRepository->findAll();
    }

    private function auditAction(int $loggedInUserId, array $entities): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::DATA_EXPORTED, $entities);
    }
}
