<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\DocumentRepository;

class GetDocumentControllerTest extends TestCase
{
    public function testHappyPath()
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
}
