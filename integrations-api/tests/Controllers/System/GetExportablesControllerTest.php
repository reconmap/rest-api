<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class GetExportablesControllerTest extends TestCase
{
    public function testGet()
    {
        $mockServerRequest = $this->createMock(ServerRequestInterface::class);

        $controller = new GetExportablesController();
        $response = $controller($mockServerRequest);

        $expectedResponse = [
            ["key" => "audit_log", "description" => "Audit log"],
            ["key" => "commands", "description" => "Commands"],
            ["key" => "documents", "description" => "Documents"],
            ["key" => "projects", "description" => "Projects"],
            ["key" => "project_templates", "description" => "Project templates"],
            ["key" => "tasks", "description" => "Tasks"],
            ['key' => 'targets', 'description' => 'Targets'],
            ["key" => "organisations", "description" => "Organisations"],
            ["key" => "users", "description" => "Users"],
            ["key" => "vulnerabilities", "description" => "Vulnerabilities"],
            ["key" => "vulnerability_categories", "description" => "Vulnerability categories"],
            ["key" => "vulnerability_templates", "description" => "Vulnerability templates"]
        ];

        $this->assertEquals($expectedResponse, $response);
    }
}
