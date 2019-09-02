<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Export\Data\Transaction;
use vvvitaly\txs\Exporters\Processors\AutoIdCounter;

final class AutoIdCounterTest extends TestCase
{
    public function testProcess(): void
    {
        $tx1 = new Transaction();
        $tx2 = new Transaction();
        $tx3 = new Transaction();

        $processor = new AutoIdCounter('test.', 100);

        $processor->process($tx1);
        $processor->process($tx2);
        $processor->process($tx3);

        $this->assertEquals('test.100', $tx1->id);
        $this->assertEquals('test.101', $tx2->id);
        $this->assertEquals('test.102', $tx3->id);
    }
}