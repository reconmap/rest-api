<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\EmailService;

class UpdateUserControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $fakeUserId = 49;

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor('{"email": "foo@bar.com"}'));
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockUserRepository->expects($this->once())
            ->method('updateById')
            ->with($fakeUserId, ['email' => 'foo@bar.com'])
            ->willReturn(true);
        $mockUserRepository->expects($this->once())
            ->method('findById')
            ->with($fakeUserId)
            ->willReturn(['full_name' => 'Some Body', 'email' => 'some.body@on.the.internet']);

        $mockEmailService = $this->createMock(EmailService::class);

        $mockPublisherService = $this->createMock(ActivityPublisherService::class);
        $mockPublisherService->expects($this->once())
            ->method('publish')
            ->with(9, AuditActions::UPDATED, 'User', ['id' => $fakeUserId]);

        $args = ['userId' => $fakeUserId];

        $controller = new UpdateUserController($mockUserRepository, $mockEmailService, $mockPublisherService);
        $response = $controller($mockRequest, $args);
        $this->assertEquals(['success' => true], $response);
    }
}
