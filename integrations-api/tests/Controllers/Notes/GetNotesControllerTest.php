<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\NoteRepository;

class GetNotesControllerTest extends TestCase
{
    public function testController()
    {
        $mockRepository = $this->createPartialMock(NoteRepository::class, ['findByParentId']);
        $mockRepository->expects($this->once())
            ->method('findByParentId')
            ->with('project', 1)
            ->willReturn([]);

        $request = (new ServerRequest('get', '/notes'))
            ->withQueryParams([
                'parentType' => 'project',
                'parentId' => '1'
            ]);

        $controller = new GetNotesController($mockRepository);
        $this->assertIsArray($controller($request));
    }
}
