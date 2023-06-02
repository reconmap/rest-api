<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ConsecutiveParamsTrait;
use Reconmap\Models\User;

class ControllerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    public function testJsonDecodeAsObject()
    {
        $subject = $this->getMockForAbstractClass(Controller::class);
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"prop": "propvalue"}');
        $decodedObject = $subject->getJsonBodyDecoded($mockRequest);

        $this->assertEquals('propvalue', $decodedObject->prop);
    }

    public function testJsonDecodeAsArray()
    {
        $subject = $this->getMockForAbstractClass(Controller::class);
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"prop": "propvalue"}');
        $decodedObject = $subject->getJsonBodyDecodedAsArray($mockRequest);

        $this->assertEquals('propvalue', $decodedObject['prop']);
    }

    public function testGetUserFromRequest()
    {
        $subject = $this->getMockForAbstractClass(Controller::class);
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->with(...$this->consecutiveParams(['userId'], ['role']))
            ->willReturnOnConsecutiveCalls(4, 'client');
        $user = $subject->getUserFromRequest($mockRequest);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(4, $user->id);
        $this->assertEquals('client', $user->role);
    }
}
