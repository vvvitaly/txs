<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Export\Data\Transaction;
use vvvitaly\txs\Core\Export\Data\TransactionSplit;
use vvvitaly\txs\Exporters\Processors\DescriptionNormalizer;

class DescriptionNormalizerTest_Callback
{
    public function __invoke()
    {
    }
}

final class DescriptionNormalizerTest extends TestCase
{
    public function testProcessDescription(): void
    {
        $funcs = [];

        for ($i = 0; $i < 3; $i++) {
            $prev = $i === 0 ? $i : $i - 1;

            $callback = $this->createMock(DescriptionNormalizerTest_Callback::class);
            $callback->expects($this->once())
                ->method('__invoke')
                ->with('test' . $prev)
                ->willReturn('test' . $i);

            $funcs[] = $callback;
        }

        $tx = new Transaction();
        $tx->description = 'test0';

        $proc = new DescriptionNormalizer($funcs);
        $proc->process($tx);
    }

    public function testProcessItems(): void
    {
        $func = static function () {
            return 'processed';
        };

        $tx = new Transaction();

        $item = new TransactionSplit();
        $item->memo = 'test';
        $tx->splits = [$item];

        $proc = new DescriptionNormalizer([$func]);
        $proc->process($tx);

        $this->assertEquals('processed', $item->memo);
    }
}