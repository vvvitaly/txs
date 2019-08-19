<?php

declare(strict_types=1);

namespace tests\Sms\Sources;

use App\Sms\Sms;
use App\Sms\Sources\SmsBackupXMLSource;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

/** @noinspection PhpMissingDocCommentInspection */

final class SmsBackupXMLSourceTest extends TestCase
{
    public function testRead(): void
    {
        $xml = <<<XML
<?xml version='1.0' encoding='UTF-8' standalone='yes' ?>
<!--File Created By SMS Backup & Restore v10.05.602 on 06/08/2019 02:10:16-->
<smses count="1" backup_set="473ffea1-6559-4eb9-8c00-dfc0fe60bf68" backup_date="1565046616253">
  <sms protocol="0" address="123" date="1526661378815" type="1" subject="null" body="some text" toa="null" sc_toa="null" service_center="+79037011111" read="1" status="-1" locked="0" date_sent="0" sub_id="0" readable_date="18 мая 2018 г. 19:36:18" contact_name="(Unknown)" />
</smses>
XML;

        $source = new SmsBackupXMLSource(simplexml_load_string($xml));
        /** @var Sms[] $smsList */
        $smsList = iterator_to_array($source->read(new DateTimeImmutable('-100 years'), new DateTimeImmutable('now')));

        $this->assertCount(1, $smsList);
        $this->assertEquals('123', $smsList[0]->from);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(new DateTimeImmutable('2018-05-18 19:36:18', new DateTimeZone('Europe/Moscow')), $smsList[0]->date);
        $this->assertEquals('some text', $smsList[0]->message);
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

        $source = new SmsBackupXMLSource(simplexml_load_string($xml));
        /** @var Sms[] $smsList */
        $smsList = iterator_to_array($source->read(new DateTimeImmutable('2018-04-19 00:00:00'),
            new DateTimeImmutable('2018-06-17 00:00:00')));

        $this->assertCount(1, $smsList);
        $this->assertEquals('1232', $smsList[0]->from);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(new DateTimeImmutable('2018-05-18 19:36:18', new DateTimeZone('Europe/Moscow')),
            $smsList[0]->date);
        $this->assertEquals('text2', $smsList[0]->message);
    }
}