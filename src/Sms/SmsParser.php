<?php

declare(strict_types=1);

namespace App\Sms;

use App\Core\Bills\BillsCollection;

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
     * Parse all SMS from the given source.
     *
     * @return BillsCollection
     */
    public function parse(): BillsCollection
    {
        $bills = [];

        foreach ($this->source->read() as $sms) {
            $bill = $this->parser->parse($sms);
            if ($bill) {
                $bills[] = $bill;
            }
        }

        return new BillsCollection(...$bills);
    }
}