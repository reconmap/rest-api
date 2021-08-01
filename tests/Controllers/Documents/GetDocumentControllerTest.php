<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use League\Route\Http\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\DocumentRepository;

class GetDocumentControllerTest extends TestCase
{
    public function testRetrievingDocument()
    {
        $mockDocument = ['title' => 'foo'];

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(DocumentRepository::class);
        $mockRepository->expects($this->once())
            ->method('findById')
            ->with(568)
            ->willReturn($mockDocument);

        $args = ['documentId' => 568];

        $controller = new GetDocumentController($mockRepository);
        $response = $controller($mockRequest, $args);

        $this->assertEquals($mockDocument, $response);
    }

    public function testRetrievingMissingDocument()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(DocumentRepository::class);
        $mockRepository->expects($this->once())
            ->method('findById')
            ->with(568)
            ->willReturn(null);

        $args = ['documentId' => 568];

        $this->expectException(NotFoundException::class);

        $controller = new GetDocumentController($mockRepository);
        $controller($mockRequest, $args);
    }
}
