<?php

declare(strict_types=1);

namespace tests\Vmestecard;

use App\Core\Bills\Bill;
use App\Core\Source\SourceReadException;
use App\Libs\Date\DatesRange;
use App\Vmestecard\Api\ApiClientInterface;
use App\Vmestecard\Api\ApiErrorException;
use App\Vmestecard\Api\Client\Pagination;
use App\Vmestecard\VmestecardSource;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/** @noinspection PhpMissingDocCommentInspection */

final class VmestecardSourceTest extends TestCase
{
    public function testParseSuccessfulResponse(): void
    {
        $response = require __DIR__ . '/successful-response.php';
        $dates = new DatesRange(new DateTimeImmutable('-1 year'), null);
        $defaultAccount = 'default';

        $client = $this->createMock(ApiClientInterface::class);
        $client->expects($this->once())
            ->method('getHistory')
            ->with($this->identicalTo($dates), $this->isInstanceOf(Pagination::class))
            ->willReturn($response);

        $source = new VmestecardSource($client, $dates, $defaultAccount);

        /** @var Bill[] $bills */
        $bills = iterator_to_array($source->read(), false);

        $this->assertCount(1, $bills);

        $this->assertEquals(new DateTimeImmutable('2019-08-03 21:21:41'), $bills[0]->getInfo()->getDate());
        $this->assertEquals($defaultAccount, $bills[0]->getAccount());
        $this->assertEquals(171.9, $bills[0]->getAmount()->getValue());
        $this->assertNull($bills[0]->getAmount()->getCurrency());
        $this->assertEquals('804.1030.200', $bills[0]->getInfo()->getNumber());

        $this->assertCount(2, $bills[0]->getItems());
        $this->assertEquals('Пакет АТЛАС 36+18*60 20мкр*1000', $bills[0]->getItems()[0]->getDescription());
        $this->assertEquals(6, $bills[0]->getItems()[0]->getAmount()->getValue());

        $this->assertEquals('Газ.вода Кока-кола ЧЕРРИ 0,5л.*24 пл.б.', $bills[0]->getItems()[1]->getDescription());
        $this->assertEquals(47.9, $bills[0]->getItems()[1]->getAmount()->getValue());
    }

    public function testParseShouldSkipNonPurchase(): void
    {
        $response = [
            'data' => [
                'allCount' => 1,
                'rows' => [
                    [
                        'id' => 'ac49fe7d-2455-4b9f-aef9-8c30c732021f',
                        'dateTime' => '2019-08-03T21:21:41Z',
                        'type' => 'RewardData',
                        'userId' => 467463,
                        'identity' => '8018156838613605',
                        'description' => 'XXX YYY',
                        'location' => [],
                        'partnerId' => '97597cfd-2a3b-4a7a-90fe-b304860f7a67',
                        'brandId' => 'e5d73498-a193-43a0-2054-97c6526587bf',
                        'brand' => [],
                        'data' => [],
                    ],
                ],
            ],
            'result' => [
                'state' => 'Success',
                'message' => null,
                'validationErrors' => null,
            ],
        ];
        $dates = new DatesRange(new DateTimeImmutable('-1 year'), null);

        $client = $this->createMock(ApiClientInterface::class);
        $client
            ->method('getHistory')
            ->willReturn($response);

        $source = new VmestecardSource($client, $dates, 'default');

        /** @var Bill[] $bills */
        $bills = iterator_to_array($source->read(), false);

        $this->assertCount(0, $bills);
    }

    public function testParseWithError(): void
    {
        $dates = new DatesRange(new DateTimeImmutable('-1 year'), null);

        $client = $this->createMock(ApiClientInterface::class);
        $client->expects($this->once())
            ->method('getHistory')
            ->with($this->identicalTo($dates))
            ->willThrowException(new ApiErrorException('test'));

        $source = new VmestecardSource($client, $dates, 'default');

        $this->expectException(SourceReadException::class);
        iterator_to_array($source->read(), false);
    }
}