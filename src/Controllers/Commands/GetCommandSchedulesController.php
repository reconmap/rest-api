<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\SecureController;
use Reconmap\Repositories\CommandScheduleRepository;
use Reconmap\Services\Security\AuthorisationService;

class GetCommandSchedulesController extends SecureController
{
    public function __construct(AuthorisationService                       $authorisationService,
                                private readonly CommandScheduleRepository $repository)
    {
        parent::__construct($authorisationService);
    }

    protected function getPermissionRequired(): string
    {
        return 'commands.*';
    }

    public function process(ServerRequestInterface $request, array $args): array
    {
        $commandId = intval($args['commandId']);
        return $this->repository->findByCommandId($commandId);
    }
}
