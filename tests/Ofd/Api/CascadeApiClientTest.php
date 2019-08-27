<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Ofd\Api;

use App\Ofd\Api\ApiClientInterface;
use App\Ofd\Api\CascadeApiClient;
use App\Ofd\Api\OfdCheque;
use App\Ofd\Api\OfdRequest;
use PHPUnit\Framework\TestCase;

final class CascadeApiClientTest extends TestCase
{
    public function testGetChequeShouldStopOnFound(): void
    {
        $req = OfdRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1');
        $cheque = new OfdCheque();

        $inner1 = $this->createMock(ApiClientInterface::class);
        $inner1->expects($this->once())
            ->method('getCheque')
            ->with($this->identicalTo($req))
            ->willReturn(null);

        $inner2 = $this->createMock(ApiClientInterface::class);
        $inner2->expects($this->once())
            ->method('getCheque')
            ->with($this->identicalTo($req))
            ->willReturn($cheque);

        $inner3 = $this->createMock(ApiClientInterface::class);
        $inner3->expects($this->never())
            ->method('getCheque');

        $client = new CascadeApiClient($inner1, $inner2, $inner3);
        $actual = $client->getCheque($req);

        $this->assertSame($cheque, $actual);
    }
}