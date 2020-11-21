<?php
declare(strict_types=1);

namespace Reconmap\Controllers;


use Monolog\Logger;
use mysqli;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Reports\GenerateReportController;
use Reconmap\DatabaseTestCase;
use Reconmap\Models\Project;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\Config;
use Reconmap\Services\TemplateEngine;


class GenerateReportControllerTest extends DatabaseTestCase
{
    public function setUp(): void
    {
        define('RECONMAP_APP_DIR', realpath(__DIR__ . '/../../'));
        parent::setUp();
    }

    public function testGenerateResponseWithHtmlContentTypeIfFormatIsNotSet(): void
    {
        // GIVEN
        $db = $this->getDatabaseConnection();
        $this->prePopulateDatabase($db);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->willReturn(1);
        $args = ['id' => 1];

        $generateReportController = $this->createGenerateReportController($db);

        // WHEN
        /** @var GenerateReportController $generateReportController */
        $response = $generateReportController($request, $args);

        // THEN
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeader('Content-type')[0]);
    }

    /**
     * @param mysqli $db
     */
    public function prePopulateDatabase(mysqli $db): void
    {
        $project = new Project();
        $project->clientId = 1;
        $project->name = 'a project ' . time();
        $project->description = 'a description';
        $project->isTemplate = false;
        (new ProjectRepository($db))->insert($project);

        (new UserRepository($db))->create((object)[
            'name' => 'user name',
            'password' => 'password',
            'email' => 'email@email.com',
            'role' => 'creator'
        ]);
    }

    /**
     * @param mysqli $db
     * @return MockObject
     */
    public function createGenerateReportController(mysqli $db): MockObject
    {
        /** @var GenerateReportController $generateReportController */
        $generateReportController = $this->getMockBuilder(GenerateReportController::class)
            ->setConstructorArgs([
                    $this->createMock(Logger::class),
                    $db,
                    $this->createMock(TemplateEngine::class)]
            )
            ->onlyMethods(['createHtml'])
            ->getMock();
        $config = new Config();
        $config->update('appDir', dirname(__DIR__, 2));
        $generateReportController->setConfig($config);
        $generateReportController->method('createHtml')
            ->willReturn('<html lang="en">this is an html report</html>');

        return $generateReportController;
    }
}
