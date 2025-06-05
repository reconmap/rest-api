<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Models\AuditActions\VaultAuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;
use Symfony\Component\HttpFoundation\Response;

class UpdateVaultItemControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $project_id = 3;
        $vault_item_id = 44;
        $user_id = 55;

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor('{"anything":"will not be in the filtered array","name":"compromised account","value":"secret text","note":"Some note for other testers","type":"password","password":"UltimateP4ssw0rd!"}'));
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn($user_id);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with($user_id, AuditActions::UPDATED, 'Vault Item', ['compromised account']);

        $mockRepository = $this->createMock(VaultRepository::class);
        $mockRepository->expects($this->once())
            ->method('updateVaultItemById')
            ->with($vault_item_id, $project_id, "UltimateP4ssw0rd!", ['name' => 'compromised account', 'value' => 'secret text', 'note' => 'Some note for other testers', 'type' => 'password'])
            ->willReturn(true);

        $args = ['projectId' => $project_id, 'vaultItemId' => $vault_item_id];

        $controller = new UpdateVaultItemController($mockRepository, $mockAuditLogService);
        $response = $controller($mockRequest, $args);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}
