<?php declare(strict_types=1);

namespace Reconmap\Services\Filesystem;

use PHPUnit\Framework\TestCase;

class DirectoryCheckerTest extends TestCase
{
    public function testInvalidDirectory()
    {
        $checker = new DirectoryChecker();
        $response = $checker->checkDirectoryIsWriteable('/aaa/bbb/ccc');
        $expected = [
            'location' => '/aaa/bbb/ccc',
            'exists' => false,
            'writeable' => false
        ];
        $this->assertEquals($expected, $response);
    }

    public function testValidDirectory()
    {
        $checker = new DirectoryChecker();
        $response = $checker->checkDirectoryIsWriteable(__DIR__);
        $expected = [
            'location' => __DIR__,
            'exists' => true,
            'writeable' => true
        ];
        $this->assertEquals($expected, $response);
    }
}
