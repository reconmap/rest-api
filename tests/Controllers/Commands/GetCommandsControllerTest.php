<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Repositories\SearchCriterias\CommandSearchCriteria;
use Reconmap\Services\RequestPaginator;

class GetCommandsControllerTest extends TestCase
{
    public function testGetCommandsByKeywords()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getQueryParams')
            ->willReturn(['limit' => 5, 'keywords' => 'foo']);

        $mockRepository = $this->createMock(CommandRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->with($this->isInstanceOf(CommandSearchCriteria::class), $this->isInstanceOf(RequestPaginator::class))
            ->willReturn([]);

        $mockSearchCriteria = $this->createMock(CommandSearchCriteria::class);
        $mockSearchCriteria->expects($this->once())
            ->method('addKeywordsCriterion')
            ->with('foo');

        $controller = new GetCommandsController($mockRepository, $mockSearchCriteria);
        $response = $controller($mockRequest);

        $this->assertEquals([], $response);
    }
}
