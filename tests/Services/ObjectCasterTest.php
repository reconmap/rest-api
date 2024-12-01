<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;
use Reconmap\CommandOutputParsers\Models\Vulnerability;
use Reconmap\DomainObjects\Vulnerability as DomainObjectVulnerability;

class ObjectCasterTest extends TestCase
{
    public function testCastVulnerabilities()
    {
        $from = new DomainObjectVulnerability();
        $from->summary = 'From this object';
        $to = new \Reconmap\Models\Vulnerability();

        $castedObject = ObjectCaster::cast($to, $from);
        $this->assertInstanceOf(\Reconmap\Models\Vulnerability::class, $castedObject);
        $this->assertEquals('From this object', $castedObject->summary);
    }
}
