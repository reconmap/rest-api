<?php declare(strict_types=1);

namespace Reconmap\Services;

use Noodlehaus\Parser\Json;
use PHPUnit\Framework\TestCase;

class ApplicationConfigTest extends TestCase
{

    public function testGetSettings()
    {
        $subject = new ApplicationConfig('{"cors":{"allowedDomain":"foo.com"}}', new Json(), true);
        $this->assertEquals(['allowedDomain' => 'foo.com'], $subject->getSettings('cors'));
    }

    public function testGetAppDir()
    {
        $subject = new ApplicationConfig('{}', new Json(), true);
        $subject->setAppDir('/some/where');
        $this->assertEquals('/some/where', $subject->getAppDir());
    }

    public function testSetAppDir()
    {
        $subject = new ApplicationConfig('{"appDir": "/special/dir"}', new Json(), true);
        $this->assertEquals('/special/dir', $subject->getAppDir());
    }
}
