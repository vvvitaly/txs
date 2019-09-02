<?php

declare(strict_types=1);

namespace tests\Fdo;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Fdo\FdoJsonSource;

/** @noinspection PhpMissingDocCommentInspection */

final class FdoJsonSourceTest extends TestCase
{
    public function testRead(): void
    {
        $account = 'test123';
        $json = json_decode(file_get_contents(__DIR__ . '/fdo.json'), true);

        $source = new FdoJsonSource($json, $account);

        /** @var Bill[] $bills */
        $bills = iterator_to_array($source->read(), false);

        $this->assertCount(1, $bills);

        $this->assertEquals(new DateTimeImmutable('2019-08-10 10:53:00'), $bills[0]->getInfo()->getDate());
        $this->assertEquals($account, $bills[0]->getAccount());
        $this->assertEquals(618.02, $bills[0]->getAmount()->getValue());
        $this->assertNull($bills[0]->getAmount()->getCurrency());
        $this->assertEquals('21606', $bills[0]->getInfo()->getNumber());
        $this->assertEquals('COMPANY LTD', $bills[0]->getInfo()->getDescription());

        $this->assertCount(2, $bills[0]->getItems());
        $this->assertEquals('item #1', $bills[0]->getItems()[0]->getDescription());
        $this->assertEquals(589, $bills[0]->getItems()[0]->getAmount()->getValue());

        $this->assertEquals('item #2', $bills[0]->getItems()[1]->getDescription());
        $this->assertEquals(29.02, $bills[0]->getItems()[1]->getAmount()->getValue());
    }
}