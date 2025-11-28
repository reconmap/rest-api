<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Repositories\SearchCriterias\CommandSearchCriteria;
use Reconmap\Services\PaginationRequestHandler;

class GetCommandsControllerTest extends ControllerTestCase
{
    public function testGetCommandsByKeywords()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('role')
            ->willReturn('administrator');
        $mockRequest->expects($this->exactly(2))
            ->method('getQueryParams')
            ->willReturn(['limit' => 5, 'keywords' => 'foo']);

        $mockSearchCriteria = $this->createMock(CommandSearchCriteria::class);
        $mockSearchCriteria->expects($this->once())
            ->method('addKeywordsCriterion')
            ->with('foo');

        $mockRepository = $this->createMock(CommandRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->with($this->isInstanceOf(CommandSearchCriteria::class), $this->isInstanceOf(PaginationRequestHandler::class))
            ->willReturn(['nmap']);
        $mockRepository->expects($this->once())
            ->method('count')
            ->with($mockSearchCriteria)
            ->willReturn(4);

        $mockAuthorisationService = $this->createAuthorisationServiceMock();

        $controller = new GetCommandsController($mockAuthorisationService, $mockRepository, $mockSearchCriteria);
        $response = $controller($mockRequest);

        $this->assertEquals('["nmap"]', (string)$response->getBody());
        $this->assertEquals(4, $response->getHeaderLine('X-Total-Count'));
    }
}
