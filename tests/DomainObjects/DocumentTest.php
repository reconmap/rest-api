<?php declare(strict_types=1);

namespace Reconmap\DomainObjects;

use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    public function testFromObject()
    {
        $expectedDoc = new Document();
        $expectedDoc->title = 'Foo';
        $expectedDoc->visibility = 'public';

        $other = (object)[
            'title' => 'Foo',
            'visibility' => 'public',
            'nonDocAttribute' => 'should not show'
        ];

        $doc = Document::fromObject($other);
        $this->assertEquals($expectedDoc, $doc);
    }
}
