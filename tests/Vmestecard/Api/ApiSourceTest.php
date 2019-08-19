<?php

declare(strict_types=1);

namespace tests\Vmestecard\Api;

use App\Libs\Date\DateRange;
use App\Vmestecard\Api\ApiClientInterface;
use App\Vmestecard\Api\ApiErrorException;
use App\Vmestecard\Api\ApiSource;
use App\Vmestecard\SourceReadErrorException;
use App\Vmestecard\Transaction;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/** @noinspection PhpMissingDocCommentInspection */

final class ApiSourceTest extends TestCase
{
    public function testRead(): void
    {
        $response = [
            'data' => [
                'allCount' => 1,
                'rows' => [
                    [
                        'id' => 'ac49fe7d-2455-4b9f-aef9-8c30c732021f',
                        'dateTime' => '2019-08-03T21:21:41Z',
                        'type' => 'PurchaseData',
                        'userId' => 467463,
                        'identity' => '8018156838613605',
                        'description' => 'XXX YYY',
                        'location' => [
                            'id' => 202,
                            'locationId' => '21bc5eb9-2736-4dca-888a-daae154ca152',
                            'description' => 'XXX YYY',
                            'latitude' => 153.270937,
                            'longitude' => 134.35219,
                            'region' => null,
                            'city' =>
                                [
                                    'id' => 45,
                                    'regionId' => null,
                                    'name' => 'г.Брянск',
                                    'prefix' => 'г.',
                                ],
                            'street' => 'XXXX',
                            'house' => '1',
                            'building' => '',
                            'office' => '',
                        ],
                        'partnerId' => '97597cfd-2a3b-4a7a-90fe-b304860f7a67',
                        'brandId' => 'e5d73498-a193-43a0-2054-97c6526587bf',
                        'brand' => [
                            'externalId' => 'e5d73498-a193-43a0-2054-97c6526587bf',
                            'name' => 'Атлас',
                            'images' =>
                                [
                                    0 =>
                                        [
                                            'fileId' => 'f5655127-1831-4c73-b924-334abd3a4a62',
                                            'description' => 'user_portal',
                                        ],
                                ],
                        ],
                        'data' => [
                            '$type' => 'Loymax.History.UI.Model.HistoryPurchaseDataModel, Loymax.History.UI.Model',
                            'externalPurchaseId' => '804.1030.200',
                            'chequeItems' => [
                                [
                                    'description' => 'Пакет АТЛАС 36+18*60 20мкр*1000',
                                    'count' => 1.0,
                                    'amount' => 6.0,
                                ],
                                [
                                    'description' => 'Газ.вода Кока-кола ЧЕРРИ 0,5л.*24 пл.б.',
                                    'count' => 1.0,
                                    'amount' => 47.9,
                                ],
                            ],
                            'withdraws' => [],
                            'rewards' => [
                                [
                                    'offerExternalId' => 'beef71cb-a3de-11e9-ae5d-005056bb1a6c',
                                    'rewardType' => 'Discount',
                                    'description' => null,
                                    'amount' => [
                                        'amount' => 18.1,
                                        'currency' => 'RUB',
                                        'currencyInfo' => [
                                            'id' => 2,
                                            'name' => 'Рубли',
                                            'externalId' => '718ae69b-76be-413f-ad19-7b7e02e4a438',
                                            'description' => 'Денежная единица Российской Федерации',
                                            'isDeleted' => false,
                                            'nameCases' => [
                                                'nominative' => 'рубль',
                                                'genitive' => 'рубля',
                                                'plural' => 'рублей',
                                                'abbreviation' => 'руб.',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'isRefund' => false,
                            'chequeNumber' => '804.1030.200',
                            'amount' => [
                                'amount' => 171.9,
                                'currency' => 'RUB',
                                'currencyInfo' => [
                                    'id' => 2,
                                    'name' => 'Рубли',
                                    'externalId' => '718ae69b-76be-413f-ad19-7b7e02e4a438',
                                    'description' => 'Денежная единица Российской Федерации',
                                    'isDeleted' => false,
                                    'nameCases' => [
                                        'nominative' => 'рубль',
                                        'genitive' => 'рубля',
                                        'plural' => 'рублей',
                                        'abbreviation' => 'руб.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'result' => [
                'state' => 'Success',
                'message' => null,
                'validationErrors' => null,
            ],
        ];
        $dates = new DateRange(new DateTimeImmutable('-1 year'), null);

        $client = $this->createMock(ApiClientInterface::class);
        $client->expects($this->once())
            ->method('getHistory')
            ->with($this->identicalTo($dates))
            ->willReturn($response);

        $source = new ApiSource($client, $dates);

        /** @var Transaction[] $txs */
        $txs = iterator_to_array($source->read(), false);

        $this->assertCount(1, $txs);

        $this->assertEquals(new DateTimeImmutable('2019-08-03 21:21:41'), $txs[0]->date);
        $this->assertEquals(171.9, $txs[0]->amount);
        $this->assertEquals('804.1030.200', $txs[0]->chequeNumber);

        $this->assertCount(2, $txs[0]->items);
        $this->assertEquals('Пакет АТЛАС 36+18*60 20мкр*1000', $txs[0]->items[0]->description);
        $this->assertEquals(6, $txs[0]->items[0]->amount);

        $this->assertEquals('Газ.вода Кока-кола ЧЕРРИ 0,5л.*24 пл.б.', $txs[0]->items[1]->description);
        $this->assertEquals(47.9, $txs[0]->items[1]->amount);
    }

    public function testReadWithError(): void
    {
        $dates = new DateRange(new DateTimeImmutable('-1 year'), null);

        $client = $this->createMock(ApiClientInterface::class);
        $client->expects($this->once())
            ->method('getHistory')
            ->with($this->identicalTo($dates))
            ->willThrowException(new ApiErrorException('test'));

        $source = new ApiSource($client, $dates);

        $this->expectException(SourceReadErrorException::class);
        iterator_to_array($source->read(), false);
    }
}