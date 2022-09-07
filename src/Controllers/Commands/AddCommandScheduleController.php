<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\CommandSchedule;
use Reconmap\Repositories\CommandScheduleRepository;

class AddCommandScheduleController extends Controller
{
    public function __construct(private readonly CommandScheduleRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var CommandSchedule $commandSchedule */
        $commandSchedule = $this->getJsonBodyDecodedAsClass($request, new CommandSchedule());
        $commandSchedule->creator_uid = $request->getAttribute('userId');

        $commandSchedule->id = $this->repository->insert($commandSchedule);

        return $this->createStatusCreatedResponse($commandSchedule);
    }
}
