<?php

declare(strict_types=1);

namespace tests\Exporters\Processors;

use App\Core\Export\Data\Transaction;
use App\Exporters\Processors\CompositeProcessor;
use App\Exporters\Processors\ProcessorInterface;
use PHPUnit\Framework\TestCase;

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