<?php declare(strict_types=1);

namespace Reconmap\Controllers\Organisations;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Reconmap\Models\Organisation;
use Reconmap\Repositories\OrganisationRepository;

class GetOrganisationControllerTest extends TestCase
{
    public function testController()
    {
        $rootOrg = new Organisation();

        $mockRepository = $this->createPartialMock(OrganisationRepository::class, ['findRootOrganisation']);
        $mockRepository->expects($this->once())
            ->method('findRootOrganisation')
            ->willReturn($rootOrg);

        $request = (new ServerRequest('get', '/organisations/root'));
        $controller = new GetOrganisationController($mockRepository);
        $this->assertEquals($rootOrg, $controller($request));
    }
}
