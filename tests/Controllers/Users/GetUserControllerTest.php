<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\UserRepository;

class GetUserControllerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testHappyPath()
    {
        $mockUser = ['title' => 'foo', 'preferences' => '{"theme": "system"}'];

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(UserRepository::class);
        $mockRepository->expects($this->once())
            ->method('findById')
            ->with(568)
            ->willReturn($mockUser);

        $args = ['userId' => 568];

        $controller = new GetUserController($mockRepository);
        $response = $controller($mockRequest, $args);

        $this->assertEquals(['title' => 'foo', 'preferences' => ["theme" => "system"]], $response);
    }
}
