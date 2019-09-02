<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use SimpleXMLElement;
use vvvitaly\txs\Core\Bills\BillsCollection;
use vvvitaly\txs\Core\Source\BillSourceInterface;
use vvvitaly\txs\Core\Source\SourceReadException;
use vvvitaly\txs\Libs\Date\DatesRange;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

/**
 * Read SMS from XML file created by "SMS Backup & Restore" application (com.riteshsahu.SMSBackupRestore)
 */
final class SmsBackupXMLSource implements BillSourceInterface
{
    /**
     * @var SimpleXMLElement
     */
    private $xml;

    /**
     * @var DatesRange
     */
    private $dateRange;

    /**
     * @var MessageParserInterface
     */
    private $smsParser;

    /**
     * @param SimpleXMLElement $xml
     * @param DatesRange $dateRange
     * @param MessageParserInterface $smsParser
     */
    public function __construct(SimpleXMLElement $xml, DatesRange $dateRange, MessageParserInterface $smsParser)
    {
        $this->xml = $xml;
        $this->dateRange = $dateRange;
        $this->smsParser = $smsParser;
    }

    /**
     * @inheritDoc
     */
    public function read(): BillsCollection
    {
        $bills = [];
        foreach ($this->xml->sms as $node) {
            $time = floor((int)(string)$node['date'] / 1000);
            try {
                $date = (new DateTimeImmutable('@' . $time))->setTimezone(new DateTimeZone('Europe/Moscow'));
            } catch (Exception $e) {
                throw new SourceReadException('Can not read message date: "' . $node['date'] . '"', 0, $e);
            }

            if (!$this->dateRange->contains($date)) {
                continue;
            }

            $sms = new Message(
                (string)$node['address'],
                $date,
                (string)$node['body']
            );

            $bill = $this->smsParser->parse($sms);
            if ($bill) {
                $bills[] = $bill;
            }
        }

        return new BillsCollection(...$bills);
    }

}