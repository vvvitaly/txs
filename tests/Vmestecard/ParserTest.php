<?php

declare(strict_types=1);

namespace tests\Vmestecard;

use App\Core\Bills\Bill;
use App\Vmestecard\Parser;
use App\Vmestecard\Transaction;
use App\Vmestecard\TransactionItem;
use App\Vmestecard\TransactionsSourceInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use tests\Helpers\GeneratorHelper;

/** @noinspection PhpMissingDocCommentInspection */

final class ParserTest extends TestCase
{
    public function testParse(): void
    {
        $item1 = new TransactionItem();
        $item1->amount = 100;
        $item1->description = 'purchase #1';

        $item2 = new TransactionItem();
        $item2->amount = 99.8;
        $item2->description = 'purchase #2';

        $tx1 = new Transaction();
        $tx1->date = new DateTimeImmutable('2019-08-19 23:00:12');
        $tx1->amount = 199.8;
        $tx1->chequeNumber = '900.1000.001';
        $tx1->items = [$item1, $item2];

        $source = $this->createMock(TransactionsSourceInterface::class);
        $source->expects($this->once())
            ->method('read')
            ->willReturn(GeneratorHelper::fromArray([$tx1]));

        /** @var Bill[] $bills */
        $bills = iterator_to_array((new Parser($source, 'default account'))->parse(), false);

        $this->assertCount(1, $bills);
        $this->assertEquals('default account', $bills[0]->getAccount());
        $this->assertEquals(199.8, $bills[0]->getAmount()->getValue());
        $this->assertNull($bills[0]->getAmount()->getCurrency());
        $this->assertEquals(new DateTimeImmutable('2019-08-19 23:00:12'), $bills[0]->getInfo()->getDate());
        $this->assertNull($bills[0]->getInfo()->getDescription());
        $this->assertEquals('900.1000.001', $bills[0]->getInfo()->getNumber());

        $items = $bills[0]->getItems();
        $this->assertCount(2, $items);
        $this->assertEquals(100, $items[0]->getAmount()->getValue());
        $this->assertNull($items[0]->getAmount()->getCurrency());
        $this->assertEquals('purchase #1', $items[0]->getDescription());

        $this->assertEquals(99.8, $items[1]->getAmount()->getValue());
        $this->assertNull($items[1]->getAmount()->getCurrency());
        $this->assertEquals('purchase #2', $items[1]->getDescription());
    }
}