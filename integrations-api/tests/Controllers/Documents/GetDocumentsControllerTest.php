<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\DocumentRepository;

class GetDocumentsControllerTest extends TestCase
{
    public function testController()
    {
        $mockRepository = $this->createPartialMock(DocumentRepository::class, ['findByParentId']);
        $mockRepository->expects($this->once())
            ->method('findByParentId')
            ->with('project', 1)
            ->willReturn([]);

        $request = (new ServerRequest('get', '/documents'))
            ->withQueryParams([
                'parentType' => 'project',
                'parentId' => '1'
            ]);

        $controller = new GetDocumentsController($mockRepository);
        $this->assertIsArray($controller($request));
    }
}
