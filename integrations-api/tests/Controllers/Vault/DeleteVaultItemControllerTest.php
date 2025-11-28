<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;
use Symfony\Component\HttpFoundation\Response;

class DeleteVaultItemControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $projectId = 3;
        $vaultSecretId = 7;

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(7);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(7, AuditActions::DELETED, 'Vault Secret', [$vaultSecretId]);


        $mockRepository = $this->createMock(VaultRepository::class);
        $mockRepository->expects($this->once())
            ->method('deleteByIdAndUserId')
            ->with($vaultSecretId, 7)
            ->willReturn(true);

        $args = ['projectId' => $projectId, 'vaultItemId' => $vaultSecretId];

        $controller = new DeleteVaultItemController($mockRepository, $mockAuditLogService);
        $response = $controller($mockRequest, $args);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
