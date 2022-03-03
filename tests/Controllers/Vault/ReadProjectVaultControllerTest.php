<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Vault;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Models\AuditActions\VaultAuditActions;
use Reconmap\Services\AuditLogService;
use Fig\Http\Message\StatusCodeInterface;

class ReadProjectVaultControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $project_id = 3;
        $expected_result = ['any' => 'result', 'provided' => 'by', 'vault' => 'repository'];
        $mockRequest = $this->createMock(ServerRequestInterface::class);
    
        $mockRepository = $this->createMock(VaultRepository::class);
        $mockRepository->expects($this->once())
        ->method('findAll')
        ->with($project_id)
        ->willReturn($expected_result);

        $args = ['projectId' => $project_id];

        $controller = new ReadProjectVaultController($mockRepository);
        $response = $controller($mockRequest, $args);
        $this->assertEquals($expected_result, $response);
    }
}
