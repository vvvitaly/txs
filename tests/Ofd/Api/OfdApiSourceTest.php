<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Ofd\Api;

use App\Core\Bills\Bill;
use App\Core\Source\SourceReadException;
use App\Ofd\Api\ApiClientInterface;
use App\Ofd\Api\ApiRequestException;
use App\Ofd\Api\OfdApiSource;
use App\Ofd\Api\OfdCheque;
use App\Ofd\Api\OfdRequest;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class OfdApiSourceTest extends TestCase
{
    public function testReadWithSuccessfulApiRequest(): void
    {
        $requests = [
            OfdRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1'),
        ];

        $cheque = new OfdCheque();
        $cheque->date = new DateTimeImmutable('2019-08-27 23:45:11');
        $cheque->totalAmount = 100.23;
        $cheque->place = 'test';
        $cheque->number = '1.2.3';
        $cheque->items = [
            ['item1', 40.23],
            ['item2', 60],
        ];

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
            ->method('getCheque')
            ->with($this->identicalTo($requests[0]))
            ->willReturn($cheque);

        $source = new OfdApiSource($requests, $apiClient, 'OfdApiSourceTest');
        /** @var Bill[] $actual */
        $actual = iterator_to_array($source->read(), false);

        $this->assertCount(1, $actual);

        $this->assertEquals('OfdApiSourceTest', $actual[0]->getAccount());
        $this->assertEquals(100.23, $actual[0]->getAmount()->getValue());
        $this->assertNull($actual[0]->getAmount()->getCurrency());
        $this->assertEquals(new DateTimeImmutable('2019-08-27 23:45:11'), $actual[0]->getInfo()->getDate());
        $this->assertEquals('test', $actual[0]->getInfo()->getDescription());
        $this->assertEquals('1.2.3', $actual[0]->getInfo()->getNumber());

        $this->assertCount(2, $actual[0]->getItems());

        $this->assertEquals(40.23, $actual[0]->getItems()[0]->getAmount()->getValue());
        $this->assertEquals('item1', $actual[0]->getItems()[0]->getDescription());

        $this->assertEquals(60, $actual[0]->getItems()[1]->getAmount()->getValue());
        $this->assertEquals('item2', $actual[0]->getItems()[1]->getDescription());
    }

    public function testReadWithApiError(): void
    {
        $requests = [
            OfdRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1'),
        ];

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient
            ->method('getCheque')
            ->willThrowException(new ApiRequestException('test'));

        $source = new OfdApiSource($requests, $apiClient, 'OfdApiSourceTest');

        $this->expectException(SourceReadException::class);
        $source->read();
    }

    public function testReadShouldSkipUnknownRequests(): void
    {
        $requests = [
            OfdRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1'),
            OfdRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1'),
            OfdRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1'),
        ];

        $chequeForRequest0 = new OfdCheque();
        $chequeForRequest0->totalAmount = 100;
        $chequeForRequest2 = new OfdCheque();
        $chequeForRequest2->totalAmount = 101;

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->exactly(3))
            ->method('getCheque')
            ->withConsecutive(
                [$this->identicalTo($requests[0])],
                [$this->identicalTo($requests[1])],
                [$this->identicalTo($requests[2])]
            )
            ->willReturnOnConsecutiveCalls(
                $chequeForRequest0,
                null,
                $chequeForRequest2
            );

        $source = new OfdApiSource($requests, $apiClient, 'OfdApiSourceTest');
        /** @var Bill[] $actual */
        $actual = iterator_to_array($source->read(), false);

        $this->assertCount(2, $actual);

        $this->assertEquals(100, $actual[0]->getAmount()->getValue());
        $this->assertEquals(101, $actual[1]->getAmount()->getValue());
    }
}