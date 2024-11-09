<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\SecureController;
use Reconmap\Repositories\CommandScheduleRepository;
use Reconmap\Repositories\CommandUsageRepository;
use Reconmap\Services\Security\AuthorisationService;

class GetCommandUsagesController extends SecureController
{
    public function __construct(AuthorisationService                       $authorisationService,
                                private readonly CommandUsageRepository $repository)
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
