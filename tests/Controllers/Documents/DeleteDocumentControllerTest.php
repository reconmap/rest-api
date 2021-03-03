<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\DocumentRepository;

class DeleteDocumentControllerTest extends ControllerTestCase
{
    public function testDelete()
    {
        $mockRepository = $this->createPartialMock(DocumentRepository::class, ['deleteById']);
        $mockRepository->expects($this->once())
            ->method('deleteById')
            ->with(1)
            ->willReturn(true);

        $request = $this->createMock(ServerRequestInterface::class);
        $args = ['documentId' => 1];

        $controller = new DeleteDocumentController($mockRepository);
        $this->assertEquals(['success' => true], $controller($request, $args));
    }
}

