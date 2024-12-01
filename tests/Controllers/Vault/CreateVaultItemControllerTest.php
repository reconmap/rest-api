<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\VaultAuditActions;
use Reconmap\Models\Vault;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;
use Symfony\Component\HttpFoundation\Response;

class CreateVaultItemControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $project_id = 3;

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor('{"name":"compromised account","value":"secret text","note":"Some note for other testers","reportable":false,"type":"password","password":"UltimateP4ssw0rd!"}'));
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(7);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(7, VaultAuditActions::ITEM_CREATED, ['compromised account']);

        $vault = new Vault();
        $vault->name = "compromised account";
        $vault->value = "secret text";
        $vault->note = "Some note for other testers";
        $vault->reportable = false;
        $vault->type = "password";
        $vault->project_id = $project_id;

        $mockRepository = $this->createMock(VaultRepository::class);
        $mockRepository->expects($this->once())
            ->method('insert')
            ->with($vault, "UltimateP4ssw0rd!")
            ->willReturn(2);

        $args = ['projectId' => $project_id];

        $controller = new CreateVaultItemController($mockRepository, $mockAuditLogService);
        $response = $controller($mockRequest, $args);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}
