<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\NoteRepository;

class DeleteNoteControllerTest extends ControllerTestCase
{
    public function testDelete()
    {
        $mockRepository = $this->createPartialMock(NoteRepository::class, ['deleteById']);
        $mockRepository->expects($this->once())
            ->method('deleteById')
            ->with(1)
            ->willReturn(true);

        $request = $this->createMock(ServerRequestInterface::class);
        $args = ['noteId' => 1];

        $controller = new DeleteNoteController($mockRepository);
        $this->assertEquals(['success' => true], $controller($request, $args));
    }
}

