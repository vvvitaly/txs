<?php

declare(strict_types=1);

namespace tests\Sms;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Libs\Date\DatesRange;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\SmsBackupXMLSource;

/** @noinspection PhpMissingDocCommentInspection */

final class SmsBackupXMLSourceTest extends TestCase
{
    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Create date with default timezone.
     *
     * @param string $date
     *
     * @return DateTimeImmutable
     */
    private function createDate(string $date): DateTimeImmutable
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new DateTimeImmutable($date, new DateTimeZone('Europe/Moscow'));
    }

    public function testRead(): void
    {
        $xml = <<<XML
<?xml version='1.0' encoding='UTF-8' standalone='yes' ?>
<!--File Created By SMS Backup & Restore v10.05.602 on 06/08/2019 02:10:16-->
<smses count="2" backup_set="473ffea1-6559-4eb9-8c00-dfc0fe60bf68" backup_date="1565046616253">
  <sms protocol="0" address="1231" date="1529339778815" type="1" subject="null" body="text1" toa="null" sc_toa="null" service_center="+79037011111" read="1" status="-1" locked="0" date_sent="0" sub_id="0" readable_date="18 июня 2018 г. 19:36:18" contact_name="(Unknown)" />
  <sms protocol="0" address="1232" date="1526661378815" type="1" subject="null" body="text2" toa="null" sc_toa="null" service_center="+79037011111" read="1" status="-1" locked="0" date_sent="0" sub_id="0" readable_date="18 мая 2018 г. 19:36:18" contact_name="(Unknown)" />  
</smses>
XML;
        $dateRange = new DatesRange(new DateTimeImmutable('2000-01-01'));
        $bill1 = new Bill(new Amount(1));
        $bill2 = new Bill(new Amount(2));

        $innerParser = $this->createMock(MessageParserInterface::class);
        $innerParser->expects($this->exactly(2))
            ->method('parse')
            ->withConsecutive(
                [new Message('1231', $this->createDate('2018-06-18 19:36:18'), 'text1')],
                [new Message('1232', $this->createDate('2018-05-18 19:36:18'), 'text2')]
            )
            ->willReturnOnConsecutiveCalls(
                $bill1,
                $bill2
            );

        $source = new SmsBackupXMLSource(simplexml_load_string($xml), $dateRange, $innerParser);
        $actualList = iterator_to_array($source->read(), false);

        $this->assertCount(2, $actualList);
        $this->assertSame($bill1, $actualList[0]);
        $this->assertSame($bill2, $actualList[1]);
    }

    public function testReadWithDateFilter(): void
    {
        $xml = <<<XML
<?xml version='1.0' encoding='UTF-8' standalone='yes' ?>
<!--File Created By SMS Backup & Restore v10.05.602 on 06/08/2019 02:10:16-->
<smses count="1" backup_set="473ffea1-6559-4eb9-8c00-dfc0fe60bf68" backup_date="1565046616253">
  <sms protocol="0" address="1231" date="1529339778815" type="1" subject="null" body="text1" toa="null" sc_toa="null" service_center="+79037011111" read="1" status="-1" locked="0" date_sent="0" sub_id="0" readable_date="18 июня 2018 г. 19:36:18" contact_name="(Unknown)" />
  <sms protocol="0" address="1232" date="1526661378815" type="1" subject="null" body="text2" toa="null" sc_toa="null" service_center="+79037011111" read="1" status="-1" locked="0" date_sent="0" sub_id="0" readable_date="18 мая 2018 г. 19:36:18" contact_name="(Unknown)" />
  <sms protocol="0" address="1233" date="1524069378815" type="1" subject="null" body="text3" toa="null" sc_toa="null" service_center="+79037011111" read="1" status="-1" locked="0" date_sent="0" sub_id="0" readable_date="18 апреля 2018 г. 19:36:18" contact_name="(Unknown)" />
</smses>
XML;

        $dateRange = new DatesRange(
            new DateTimeImmutable('2018-04-19 00:00:00'),
            new DateTimeImmutable('2018-06-17 00:00:00')
        );
        $bill = new Bill(new Amount(1));

        $innerParser = $this->createMock(MessageParserInterface::class);
        $innerParser->expects($this->once())
            ->method('parse')
            ->with(new Message('1232', $this->createDate('2018-05-18 19:36:18'), 'text2'))
            ->willReturn($bill);

        $source = new SmsBackupXMLSource(simplexml_load_string($xml), $dateRange, $innerParser);
        $actualList = iterator_to_array($source->read(), false);

        $this->assertCount(1, $actualList);
        $this->assertSame($bill, $actualList[0]);
    }

    public function testParseShouldSkipSmsIfNoBill(): void
    {
        $xml = <<<XML
<?xml version='1.0' encoding='UTF-8' standalone='yes' ?>
<!--File Created By SMS Backup & Restore v10.05.602 on 06/08/2019 02:10:16-->
<smses count="2" backup_set="473ffea1-6559-4eb9-8c00-dfc0fe60bf68" backup_date="1565046616253">
  <sms protocol="0" address="1231" date="1529339778815" type="1" subject="null" body="text1" toa="null" sc_toa="null" service_center="+79037011111" read="1" status="-1" locked="0" date_sent="0" sub_id="0" readable_date="18 июня 2018 г. 19:36:18" contact_name="(Unknown)" />
  <sms protocol="0" address="1232" date="1526661378815" type="1" subject="null" body="text2" toa="null" sc_toa="null" service_center="+79037011111" read="1" status="-1" locked="0" date_sent="0" sub_id="0" readable_date="18 мая 2018 г. 19:36:18" contact_name="(Unknown)" />  
</smses>
XML;
        $dateRange = new DatesRange(new DateTimeImmutable('2000-01-01'));
        $bill = new Bill(new Amount(1));

        $sms1 = new Message('1231', $this->createDate('2018-06-18 19:36:18'), 'text1');
        $sms2 = new Message('1232', $this->createDate('2018-05-18 19:36:18'), 'text2');

        $innerParser = $this->createMock(MessageParserInterface::class);
        $innerParser->expects($this->exactly(2))
            ->method('parse')
            ->withConsecutive(
                [$sms1],
                [$sms2]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $bill
            );

        $source = new SmsBackupXMLSource(simplexml_load_string($xml), $dateRange, $innerParser);
        $actualList = iterator_to_array($source->read(), false);

        $this->assertCount(1, $actualList);
        $this->assertSame($bill, $actualList[0]);

        $skipped = $source->getSkippedMessages();
        $this->assertCount(1, $skipped);
        $this->assertEquals($sms1, $skipped[0]);
    }
}