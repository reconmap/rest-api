<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

class GetUserActivityController extends Controller
{
    public function __construct(private readonly AuditLogRepository $auditLogRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $userId = (int)$args['userId'];

        return $this->auditLogRepository->findByUserId($userId);
    }
}
