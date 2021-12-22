<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;

class ApplicationConfigTest extends TestCase
{

    public function testGetSettings()
    {
        $subject = new ApplicationConfig(["cors" => ["allowedDomain" => "foo.com"]]);
        $this->assertEquals(['allowedDomain' => 'foo.com'], $subject->getSettings('cors'));
    }

    public function testGetAppDir()
    {
        $subject = new ApplicationConfig();
        $subject->setAppDir('/some/where');
        $this->assertEquals('/some/where', $subject->getAppDir());
    }

    public function testSetAppDir()
    {
        $subject = new ApplicationConfig(["appDir" => "/special/dir"]);
        $this->assertEquals('/special/dir', $subject->getAppDir());
    }
}
