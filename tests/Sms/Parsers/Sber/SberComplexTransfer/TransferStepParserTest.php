<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\SberComplexTransfer;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\ConfirmationMessageStep;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\PinMessageStep;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\TransferStepParser;

final class TransferStepParserTest extends TestCase
{
    public function testParseStepForPinSmsTransfer(): void
    {
        $sms = new Message(
            '900',
            new DateTimeImmutable('2019-10-08 13:44:12'),
            'Для перевода 455.33р получателю SOMEBODY на VISA0002 с карты VISA0001 отправьте код 27805 на 900. Комиссия не взимается'
        );

        $parser = new TransferStepParser();
        $actual = $parser->parseStep($sms);

        /** @var PinMessageStep $actual */
        $this->assertInstanceOf(PinMessageStep::class, $actual);
        $this->assertSame($sms, $actual->getOriginSms());
        $this->assertSame(455.33, $actual->getParsedMatches()->amount);
        $this->assertEquals('р', $actual->getParsedMatches()->currency);
        $this->assertEquals('Перевод SOMEBODY на VISA0002', $actual->getParsedMatches()->description);
        $this->assertEquals('VISA0001', $actual->getParsedMatches()->senderAccount);
    }

    public function testParseStepForPinOnlineTransfer(): void
    {
        $sms = new Message(
            '900',
            new DateTimeImmutable('2019-10-08 13:44:12'),
            'Проверьте реквизиты перевода: карта списания **** 0001, карта зачисления **** 0002, сумма 1700,3 RUB. Пароль для подтверждения - 63659. Никому не сообщайте пароль.'
        );

        $parser = new TransferStepParser();
        $actual = $parser->parseStep($sms);

        /** @var PinMessageStep $actual */
        $this->assertInstanceOf(PinMessageStep::class, $actual);
        $this->assertSame($sms, $actual->getOriginSms());
        $this->assertSame(1700.3, $actual->getParsedMatches()->amount);
        $this->assertEquals('RUB', $actual->getParsedMatches()->currency);
        $this->assertEquals('Перевод на ****0002', $actual->getParsedMatches()->description);
        $this->assertEquals('0001', $actual->getParsedMatches()->senderAccount);
    }

    public function testParseStepForConfirmationSmsWithDateTime(): void
    {
        $sms = new Message(
            '900',
            new DateTimeImmutable('2019-10-08 13:44:12'),
            'VISA0001 27.09.19 08:17 перевод 455.12р Баланс: 7673.22р'
        );

        $parser = new TransferStepParser();
        $actual = $parser->parseStep($sms);

        /** @var ConfirmationMessageStep $actual */
        $this->assertInstanceOf(ConfirmationMessageStep::class, $actual);
        $this->assertSame($sms, $actual->getOriginSms());
        $this->assertEquals('VISA0001', $actual->getParsedMatches()->account);
        $this->assertSame(455.12, $actual->getParsedMatches()->amount);
        $this->assertEquals(new DateTimeImmutable('2019-09-27 08:17:00'),
            $actual->getParsedMatches()->confirmationDate);
    }

    public function testParseStepForConfirmationSmsWithDate(): void
    {
        $sms = new Message(
            '900',
            new DateTimeImmutable('2019-10-08 13:44:12'),
            'VISA0001 27.09.19 перевод 455.12р Баланс: 7673.22р'
        );

        $parser = new TransferStepParser();
        $actual = $parser->parseStep($sms);

        /** @var ConfirmationMessageStep $actual */
        $this->assertInstanceOf(ConfirmationMessageStep::class, $actual);
        $this->assertSame($sms, $actual->getOriginSms());
        $this->assertEquals('VISA0001', $actual->getParsedMatches()->account);
        $this->assertSame(455.12, $actual->getParsedMatches()->amount);
        $this->assertEquals(new DateTimeImmutable('2019-09-27 00:00:00'),
            $actual->getParsedMatches()->confirmationDate);
    }

    public function testParseStepForConfirmationSmsWithTime(): void
    {
        $sms = new Message(
            '900',
            new DateTimeImmutable('2019-10-08 13:44:12'),
            'VISA0001 08:17 перевод 455.12р Баланс: 7673.22р'
        );

        $parser = new TransferStepParser();
        $actual = $parser->parseStep($sms);

        /** @var ConfirmationMessageStep $actual */
        $this->assertInstanceOf(ConfirmationMessageStep::class, $actual);
        $this->assertSame($sms, $actual->getOriginSms());
        $this->assertEquals('VISA0001', $actual->getParsedMatches()->account);
        $this->assertSame(455.12, $actual->getParsedMatches()->amount);
        $this->assertEquals(new DateTimeImmutable('2019-10-08 08:17:00'),
            $actual->getParsedMatches()->confirmationDate);
    }

    public function testParseStepForTransferWithDescription(): void
    {
        $sms = new Message(
            '900', new DateTimeImmutable(),
            'С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.'
        );
        $this->assertNull((new TransferStepParser())->parseStep($sms));
    }

    public function testParseStepForOtherSms(): void
    {
        $sms = new Message('900', new DateTimeImmutable(), 'ECMC1234 02:38 Оплата 100р TELE2 Баланс: 14074.22р');
        $this->assertNull((new TransferStepParser())->parseStep($sms));
    }
}