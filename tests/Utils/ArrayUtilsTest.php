<?php declare(strict_types=1);

namespace Reconmap\Utils;

use PHPUnit\Framework\TestCase;

class ArrayUtilsTest extends TestCase
{
    public function testFlattenNull()
    {
        $this->assertEquals([], ArrayUtils::flatten(null));
    }

    public function testFlattenOneDimension()
    {
        $contact = ['name' => 'Foo', 'phone' => '+34 123'];
        $this->assertEquals([
            'contact_name' => 'Foo',
            'contact_phone' => '+34 123'
        ], ArrayUtils::flatten($contact, 'contact_'));
    }

    public function testFlattenTwoDimension()
    {
        $contact = [
            'name' => 'Foo',
            'phone' => '+34 123',
            'emails' => [
                'home' => 'foo@bar.com',
                'work' => 'bar@foo.com',
            ]
        ];
        $this->assertEquals([
            'contact_name' => 'Foo',
            'contact_phone' => '+34 123',
            'contact_emails.home' => 'foo@bar.com',
            'contact_emails.work' => 'bar@foo.com',
        ], ArrayUtils::flatten($contact, 'contact_'));
    }
}
