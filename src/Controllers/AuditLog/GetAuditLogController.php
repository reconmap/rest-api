<?php declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use GeoIp2\Database\Reader;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\SecureController;
use Reconmap\Repositories\AuditLogRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\PaginationRequestHandler;
use Reconmap\Services\Security\AuthorisationService;

class GetAuditLogController extends SecureController
{
    private const PAGE_LIMIT = 20;

    public function __construct(AuthorisationService                $authorisationService,
                                private readonly AuditLogRepository $repository,
                                private readonly ApplicationConfig  $config
    )
    {
        parent::__construct($authorisationService);
    }

    public function getPermissionRequired(): string
    {
        return 'auditlog.get';
    }

    protected function process(ServerRequestInterface $request): array|ResponseInterface
    {
        $paginator = new PaginationRequestHandler($request);
        $params = $request->getQueryParams();
        $currentPage = $paginator->getCurrentPage();
        $limit = isset($params['limit']) ? intval($params['limit']) : self::PAGE_LIMIT;

        $auditLogEntries = $this->repository->findAll($currentPage, $limit);

        $integrations = $this->config['integrations'];
        if (isset($integrations['maxmind']) && $integrations['maxmind']['enabled']) {
            try {
                $reader = new Reader($integrations['maxmind']['dbPath']);

                foreach ($auditLogEntries as &$entry) {
                    try {
                        $record = $reader->city($entry['client_ip']);
                        $entry['user_location'] = implode(', ', array_filter([$record->city->name, $record->country->name]));
                    } catch (\Exception $e) {
                        $this->logger->error($e);
                        $entry['user_location'] = null;
                    }
                }

                $reader->close();
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }

        $count = $this->repository->countAll();
        $pageCount = $paginator->calculatePageCount($count);

        $response = new Response;
        $response->getBody()->write(json_encode($auditLogEntries));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Page-Count')
            ->withHeader('X-Page-Count', $pageCount);
    }
}
