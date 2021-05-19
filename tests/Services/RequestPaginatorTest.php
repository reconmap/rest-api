<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RequestPaginatorTest extends TestCase
{

    public function testGetCurrentPage()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['page' => 5]);

        $paginator = new RequestPaginator($mockRequest);
        $this->assertEquals(5, $paginator->getCurrentPage());
    }

    public function testCalculatePageCount()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([]);

        $paginator = new RequestPaginator($mockRequest);
        $this->assertEquals(45, $paginator->calculatePageCount(900));
    }
}
