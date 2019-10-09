<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use tests\Helpers\TransactionHelper;
use vvvitaly\txs\Exporters\Processors\DescriptionAsAccount;

final class DescriptionAsAccountTest extends TestCase
{
    public function testProcessForSingleTransaction(): void
    {
        $singleTx = TransactionHelper::createTransaction([
            'date' => new DateTimeImmutable('2019-08-13'),
            'account' => 'cc',
            'description' => 'credit payment',
            'amount' => -100.12,
            'currency' => 'RUB',
            'hasItems' => false,
            'splits' => [
                [
                    'amount' => 100.12,
                ],
            ],
        ]);

        $processor = new DescriptionAsAccount();
        $processor->process($singleTx);

        $this->assertEquals('credit payment', $singleTx->splits[0]->account);
    }

    public function testProcessForSingleTransactionWhenAccountSetAlready(): void
    {
        $singleTx = TransactionHelper::createTransaction([
            'date' => new DateTimeImmutable('2019-08-13'),
            'account' => 'cc',
            'description' => 'credit payment',
            'amount' => -100.12,
            'hasItems' => false,
            'splits' => [
                [
                    'amount' => 100.12,
                    'account' => 'bank',
                ],
            ],
        ]);

        $processor = new DescriptionAsAccount();
        $processor->process($singleTx);

        $this->assertEquals('bank', $singleTx->splits[0]->account);
    }

    public function testProcessForSplitTransaction(): void
    {
        $splitTx = TransactionHelper::createTransaction([
            'date' => new DateTimeImmutable('2019-08-15'),
            'account' => 'cash',
            'description' => 'shopping',
            'amount' => -123.45,
            'hasItems' => true,
            'splits' => [
                [
                    'amount' => 40,
                    'account' => 'expense:tomatoes',
                    'memo' => 'tomatoes',
                ],
                [
                    'amount' => 60,
                    'memo' => 'apples',
                ],
                [
                    'amount' => 23.45,
                    'memo' => 'coffee',
                ],
            ],
        ]);

        $processor = new DescriptionAsAccount();
        $processor->process($splitTx);

        $this->assertEquals('expense:tomatoes', $splitTx->splits[0]->account);
        $this->assertEquals('apples', $splitTx->splits[1]->account);
        $this->assertEquals('coffee', $splitTx->splits[2]->account);
    }

    public function testProcessForSplitTransactionWithOneItem(): void
    {
        $splitTx = TransactionHelper::createTransaction([
            'date' => new DateTimeImmutable('2019-08-15'),
            'account' => 'cash',
            'description' => 'shopping',
            'amount' => -60,
            'hasItems' => true,
            'splits' => [
                [
                    'amount' => 60,
                    'memo' => 'apples',
                ],
            ],
        ]);

        $processor = new DescriptionAsAccount();
        $processor->process($splitTx);

        $this->assertEquals('apples', $splitTx->splits[0]->account);
    }
}