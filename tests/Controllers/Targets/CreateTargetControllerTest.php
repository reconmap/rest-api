<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Models\Target;
use Reconmap\Repositories\TargetRepository;

class CreateTargetControllerTest extends ControllerTestCase
{
    public function testSuccess(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode(['project_id' => 1, 'name' => '192.168.0.1', 'kind' => 'host']));

        $target = new Target();
        $target->project_id = 1;
        $target->name = '192.168.0.1';
        $target->kind = 'host';

        $mockVulnerabilityRepository = $this->createPartialMock(TargetRepository::class, ['insert']);
        $mockVulnerabilityRepository->expects($this->once())
            ->method('insert')
            ->with($target)
            ->willReturn(5);

        $controller = $this->injectController(new CreateTargetController($mockVulnerabilityRepository));
        $response = $controller($request);

        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals('{"targetId":5}', (string)$response->getBody());
    }
}
