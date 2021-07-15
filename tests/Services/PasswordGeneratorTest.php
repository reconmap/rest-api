<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;

class PasswordGeneratorTest extends TestCase
{
    public function testGeneration()
    {
        $generator = new PasswordGenerator();
        $password = $generator->generate(4);
        $this->assertEquals(4, strlen($password));
    }
}
