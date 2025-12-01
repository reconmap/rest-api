<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\CommandUsage;
use Reconmap\Repositories\CommandUsageRepository;

class AddCommandUsageController extends Controller
{
    public function __construct(private readonly CommandUsageRepository $repository) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var CommandUsage $commandSchedule */
        $commandSchedule = $this->getJsonBodyDecodedAsClass($request, new CommandUsage());
        $commandSchedule->createdByUid = $request->getAttribute('userId');

        $commandSchedule->id = $this->repository->insert($commandSchedule);

        return $this->createStatusCreatedResponse($commandSchedule);
    }
}
