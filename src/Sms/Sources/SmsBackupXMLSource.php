<?php

declare(strict_types=1);

namespace App\Sms\Sources;

use App\Sms\Sms;
use App\Sms\SmsSourceInterface;
use App\Sms\SourceReadErrorException;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Generator;
use SimpleXMLElement;

/**
 * Read SMS from XML file created by "SMS Backup & Restore" application (com.riteshsahu.SMSBackupRestore)
 */
final class SmsBackupXMLSource implements SmsSourceInterface
{
    /**
     * @var SimpleXMLElement
     */
    private $xml;

    /**
     * @param SimpleXMLElement $xml
     */
    public function __construct(SimpleXMLElement $xml)
    {
        $this->xml = $xml;
    }

    /**
     * @inheritDoc
     */
    public function read(): Generator
    {
        foreach ($this->xml->sms as $node) {
            $time = floor((int)(string)$node['date'] / 1000);
            try {
                $date = (new DateTimeImmutable('@' . $time))->setTimezone(new DateTimeZone('Europe/Moscow'));
            } catch (Exception $e) {
                throw new SourceReadErrorException('Can not read message date: "' . $node['date'] . '"', 0, $e);
            }

            yield new Sms(
                (string)$node['address'],
                $date,
                (string)$node['body']
            );
        }
    }

}