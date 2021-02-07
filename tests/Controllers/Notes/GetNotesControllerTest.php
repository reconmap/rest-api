<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use GuzzleHttp\Psr7\ServerRequest;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\NoteRepository;
use Reconmap\Services\TemplateEngine;

class GetNotesControllerTest extends TestCase
{
    public function testController()
    {
        $mockLogger = $this->createMock(Logger::class);
        $mockDb = $this->createMock(\mysqli::class);
        $mockTemplateEngine = $this->createMock(TemplateEngine::class);
        $mockRepository = $this->getMockBuilder(NoteRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findByParentId'])
            ->getMock();
        $mockRepository->expects($this->once())
            ->method('findByParentId')
            ->with('project', 1)
            ->willReturn([]);

        $request = new ServerRequest('get', '/notes');
        $request = $request->withQueryParams([
            'parentType' => 'project',
            'parentId' => '1'
        ]);

        $controller = new GetNotesController($mockLogger, $mockDb, $mockTemplateEngine, $mockRepository);
        $this->assertIsArray($controller($request));
    }
}
