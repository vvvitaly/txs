<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Fdo\Api;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Source\SourceReadException;
use vvvitaly\txs\Fdo\Api\ApiClientInterface;
use vvvitaly\txs\Fdo\Api\ApiErrorException;
use vvvitaly\txs\Fdo\Api\FdoCheque;
use vvvitaly\txs\Fdo\Api\FdoChequeItem;
use vvvitaly\txs\Fdo\Api\FdoQrSource;
use vvvitaly\txs\Fdo\Api\FdoRequest;

final class FdoQrSourceTest extends TestCase
{
    public function testReadWithSuccessfulApiRequest(): void
    {
        $requests = [
            FdoRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1'),
        ];

        $cheque = new FdoCheque();
        $cheque->date = new DateTimeImmutable('2019-08-27 23:45:11');
        $cheque->totalAmount = 100.23;
        $cheque->place = 'test';
        $cheque->number = '1.2.3';
        $cheque->items = [
            new FdoChequeItem('item1', 40.23),
            new FdoChequeItem('item2', 60),
        ];

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
            ->method('getCheque')
            ->with($this->identicalTo($requests[0]))
            ->willReturn($cheque);

        $source = new FdoQrSource($requests, $apiClient, 'FdoQrSourceTest');
        /** @var Bill[] $actual */
        $actual = iterator_to_array($source->read(), false);

        $this->assertCount(1, $actual);

        $this->assertEquals('FdoQrSourceTest', $actual[0]->getAccount());
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
            FdoRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1'),
        ];

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient
            ->method('getCheque')
            ->willThrowException(new ApiErrorException('test'));

        $source = new FdoQrSource($requests, $apiClient, 'FdoQrSourceTest');

        $this->expectException(SourceReadException::class);
        $source->read();
    }

    public function testReadShouldSkipUnknownRequests(): void
    {
        $requests = [
            FdoRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1'),
            FdoRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1'),
            FdoRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1'),
        ];

        $chequeForRequest0 = new FdoCheque();
        $chequeForRequest0->totalAmount = 100;
        $chequeForRequest2 = new FdoCheque();
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

        $source = new FdoQrSource($requests, $apiClient, 'FdoQrSourceTest');
        /** @var Bill[] $actual */
        $actual = iterator_to_array($source->read(), false);

        $this->assertCount(2, $actual);

        $this->assertEquals(100, $actual[0]->getAmount()->getValue());
        $this->assertEquals(101, $actual[1]->getAmount()->getValue());
    }
}