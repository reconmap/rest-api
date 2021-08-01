<?php

namespace Reconmap\Controllers\Documents;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\DocumentRepository;

class CreateDocumentControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $expectedCommand = new \stdClass();
        $expectedCommand->title = 'Just a DOC';

        $mockDocumentRepository = $this->createMock(DocumentRepository::class);
        $mockDocumentRepository->expects($this->once())
            ->method('insert')
            ->with(9, $expectedCommand)
            ->willReturn(1);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"title": "Just a DOC"}');

        $controller = new CreateDocumentController($mockDocumentRepository);
        $response = $controller($mockRequest);

        $this->assertEquals(1, $response['success']);
    }
}
