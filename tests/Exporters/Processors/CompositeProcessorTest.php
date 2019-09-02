<?php

declare(strict_types=1);

namespace tests\Exporters\Processors;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Export\Data\Transaction;
use vvvitaly\txs\Exporters\Processors\CompositeProcessor;
use vvvitaly\txs\Exporters\Processors\ProcessorInterface;

/** @noinspection PhpMissingDocCommentInspection */

final class CompositeProcessorTest extends TestCase
{
    public function testProcess(): void
    {
        $tx = new Transaction();

        $inner = [];
        for ($i = 1; $i <= 3; $i++) {
            $mock = $this->createMock(ProcessorInterface::class);
            $mock->expects($this->once())
                ->method('process')
                ->with($this->identicalTo($tx));

            $inner[] = $mock;
        }

        $composite = new CompositeProcessor(...$inner);
        $composite->process($tx);
    }
}