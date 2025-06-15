<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Models\Vault;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;
use Symfony\Component\HttpFoundation\Response;

class ReadVaultItemControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $project_id = 3;
        $vault_item_id = 7;
        $user_id = 5;

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor('{"password":"P4ssw0rd!"}'));
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn($user_id);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with($user_id, AuditActions::READ, 'Vault Item', ['compromised account']);

        $vault = new Vault();
        $vault->name = "compromised account";
        $vault->value = "secret text";
        $vault->note = "Some note for other testers";
        $vault->type = "password";
        $vault->project_id = $project_id;

        $mockRepository = $this->createMock(VaultRepository::class);
        $mockRepository->expects($this->once())
            ->method('readVaultItem')
            ->with($project_id, $vault_item_id, "P4ssw0rd!")
            ->willReturn($vault);

        $args = ['projectId' => $project_id, 'vaultItemId' => $vault_item_id];

        $controller = new ReadVaultItemController($mockRepository, $mockAuditLogService);
        $response = $controller($mockRequest, $args);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}
