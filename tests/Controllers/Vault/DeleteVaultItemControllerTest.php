<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Vault;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Models\AuditActions\VaultAuditActions;
use Reconmap\Services\AuditLogService;

class DeleteVaultItemControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $project_id = 3;
        $vault_item_id = 7;
        $vault_item_name = "name of the item";

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(7);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(7, VaultAuditActions::ITEM_DELETED, [$project_id, $vault_item_id, $vault_item_name]);


        $mockRepository = $this->createMock(VaultRepository::class);
        $mockRepository->expects($this->once())
        ->method('getVaultItemName')
        ->with($vault_item_id, $project_id)
        ->willReturn($vault_item_name);
        $mockRepository->expects($this->once())
        ->method('deleteByIdAndProjectId')
        ->with($vault_item_id, $project_id)
        ->willReturn(true);

        $args = ['projectId' => $project_id, 'vaultItemId' => $vault_item_id];

        $controller = new DeleteVaultItemController($mockRepository, $mockAuditLogService);
        $response = $controller($mockRequest, $args);
        $this->assertEquals(['success' => true], $response);
    }
}
