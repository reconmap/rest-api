<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\DocumentRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

class DeleteDocumentControllerTest extends ControllerTestCase
{
    public function testDelete()
    {
        $mockAuthorisationService = $this->createMock(AuthorisationService::class);
        $mockAuthorisationService->expects($this->once())
            ->method('isRoleAllowed')
            ->with('superuser', 'document.delete')
            ->willReturn(true);

        $mockActivityPublisherService = $this->createMock(ActivityPublisherService::class);
        $mockActivityPublisherService->expects($this->once())
            ->method('publish');

        $mockRepository = $this->createPartialMock(DocumentRepository::class, ['deleteById']);
        $mockRepository->expects($this->once())
            ->method('deleteById')
            ->with(1)
            ->willReturn(true);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive(['role'], ['userId'])
            ->willReturnOnConsecutiveCalls('superuser', 1);

        $args = ['documentId' => 1];

        $controller = new DeleteDocumentController($mockAuthorisationService, $mockActivityPublisherService, $mockRepository);
        $response = $controller($request, $args);
        $this->assertEquals(204, $response->getStatusCode());
    }
}

