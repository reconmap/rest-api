<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\SecureController;
use Reconmap\Repositories\CommandScheduleRepository;
use Reconmap\Services\Security\AuthorisationService;

class GetCommandsSchedulesController extends SecureController
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

    public function process(ServerRequestInterface $request): array
    {
        return $this->repository->findAll();
    }
}
