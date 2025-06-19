<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;
use Symfony\Component\HttpFoundation\Response;

class UpdateVaultItemControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $projectId = 3;
        $vaultItemId = 44;
        $userId = 55;

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor('{"anything":"will not be in the filtered array","name":"compromised account","value":"secret text","note":"Some note for other testers","type":"password","password":"UltimateP4ssw0rd!"}'));
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn($userId);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with($userId, AuditActions::UPDATED, 'Vault Item', ['compromised account']);

        $mockRepository = $this->createMock(VaultRepository::class);
        $mockRepository->expects($this->once())
            ->method('updateVaultItemById')
            ->with($vaultItemId, "UltimateP4ssw0rd!", ['name' => 'compromised account', 'value' => 'secret text', 'note' => 'Some note for other testers', 'type' => 'password'])
            ->willReturn(true);

        $args = ['projectId' => $projectId, 'vaultItemId' => $vaultItemId];

        $controller = new UpdateVaultItemController($mockRepository, $mockAuditLogService);
        $response = $controller($mockRequest, $args);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}
