<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\SberOrder;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\ConfirmationMessageStep;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\OrderStepParser;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\PinMessageStep;

final class OrderStepParserTest extends TestCase
{
    public function testParseStepForPinSms(): void
    {
        $sms = new Message(
            '900',
            new DateTimeImmutable('2019-10-08 13:44:12'),
            'Проверьте реквизиты платежа: интернет-магазин SOME STORE NAME, заказ: XXX-1234567.89.0, сумма: 345.12 руб. Для оплаты с карты VISA0001 отправьте пароль 70443 на номер 900.'
        );

        $parser = new OrderStepParser();
        $actual = $parser->parseStep($sms);

        /** @var PinMessageStep $actual */
        $this->assertInstanceOf(PinMessageStep::class, $actual);
        $this->assertSame($sms, $actual->getOriginSms());
        $this->assertSame('VISA0001', $actual->getParsedMatches()->account);
        $this->assertSame('SOME STORE NAME', $actual->getParsedMatches()->store);
        $this->assertSame('XXX-1234567.89.0', $actual->getParsedMatches()->orderId);
    }

    public function testParseStepForConfirmationSms(): void
    {
        $sms = new Message(
            '900',
            new DateTimeImmutable('2019-10-08 13:44:12'),
            'Заказ XXX-1234567.89.0 в магазине SOME STORE NAME на сумму 300,30 руб. успешно оплачен.'
        );

        $parser = new OrderStepParser();
        $actual = $parser->parseStep($sms);

        /** @var ConfirmationMessageStep $actual */
        $this->assertInstanceOf(ConfirmationMessageStep::class, $actual);
        $this->assertSame($sms, $actual->getOriginSms());
        $this->assertSame(300.3, $actual->getParsedMatches()->amount);
        $this->assertSame('SOME STORE NAME', $actual->getParsedMatches()->store);
        $this->assertSame('XXX-1234567.89.0', $actual->getParsedMatches()->orderId);
    }

    public function testParseStepForOtherSms(): void
    {
        $sms = new Message('900', new DateTimeImmutable(), 'ECMC1234 02:38 Оплата 100р TELE2 Баланс: 14074.22р');
        $this->assertNull((new OrderStepParser())->parseStep($sms));
    }
}