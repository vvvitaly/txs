<?php

declare(strict_types=1);

namespace App\Sms;

use App\Core\Bills\BillsCollection;
use DateTimeImmutable;

/**
 * Parse SMS from the given source
 */
final class SmsParser
{
    /**
     * @var SmsSourceInterface
     */
    private $source;

    /**
     * @var MessageParserInterface
     */
    private $parser;

    /**
     * @param SmsSourceInterface $source
     * @param MessageParserInterface $parser
     */
    public function __construct(SmsSourceInterface $source, MessageParserInterface $parser)
    {
        $this->source = $source;
        $this->parser = $parser;
    }

    /**
     * Parse all SMS from the given source, filtered by date
     *
     * @param DateTimeImmutable $dateBegin
     * @param DateTimeImmutable $dateEnd
     *
     * @return BillsCollection
     */
    public function parse(DateTimeImmutable $dateBegin, DateTimeImmutable $dateEnd): BillsCollection
    {
        $bills = [];

        foreach ($this->source->read($dateBegin, $dateEnd) as $sms) {
            $bill = $this->parser->parse($sms);
            if ($bill) {
                $bills[] = $bill;
            }
        }

        return new BillsCollection(...$bills);
    }
}