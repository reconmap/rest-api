<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Reconmap\ConsecutiveParamsTrait;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\NoteRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

class DeleteNoteControllerTest extends ControllerTestCase
{
    use ConsecutiveParamsTrait;

    public function testDelete()
    {
        $mockRepository = $this->createPartialMock(NoteRepository::class, ['deleteById']);
        $mockRepository->expects($this->once())
            ->method('deleteById')
            ->with(1)
            ->willReturn(true);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getUri')
            ->willReturn(new Uri('http://localhost/notes/1'));
        $request->expects($this->exactly(2))
            ->method('getAttribute')
            ->with(...$this->consecutiveParams(['role'], ['userId']))
            ->willReturnOnConsecutiveCalls('superuser', 1);
        $args = ['entityId' => 1];

        $mockAuthorisationService = $this->createMock(AuthorisationService::class);
        $mockAuthorisationService->expects($this->once())
            ->method('isRoleAllowed')
            ->with('superuser', 'note.delete')
            ->willReturn(true);
        $mockActivityPublisherService = $this->createMock(ActivityPublisherService::class);

        $mockLogger = $this->createMock(LoggerInterface::class);

        $controller = new DeleteNoteController($mockAuthorisationService, $mockActivityPublisherService, $mockRepository);
        $controller->setLogger($mockLogger);
        $response = $controller($request, $args);
        $this->assertEquals(204, $response->getStatusCode());;
    }
}

