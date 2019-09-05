<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo\Api\Clients;

use DateTimeImmutable;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\CssSelector\Exception\ParseException;
use vvvitaly\txs\Fdo\Api\FdoCheque;
use vvvitaly\txs\Fdo\Api\FdoChequeItem;
use vvvitaly\txs\Libs\Html\DomHelper;
use vvvitaly\txs\Libs\Html\DOMLoadingException;

/**
 * Default parser implementation (parse HTML)
 */
final class TaxcomParser
{
    /**
     * @var CssSelectorConverter
     */
    private $selector;

    /**
     */
    public function __construct()
    {
        $this->selector = new CssSelectorConverter();
    }

    /**
     * @param ResponseInterface $response
     *
     * @return FdoCheque|null
     * @throws ParseException
     */
    public function parse(ResponseInterface $response): ?FdoCheque
    {
        $cheque = new FdoCheque();
        $html = $response->getBody()->getContents();

        try {
            $doc = DomHelper::loadDocument($html);
        } catch (DOMLoadingException $exception) {
            throw new ResponseParseException('Can not load DOM from response', 0, $exception);
        }

        $xpath = new DOMXPath($doc);

        $el = $this->find($xpath, '.receipt-company-name span.value');
        if (!$el[2]) {
            return null;
        }

        $cheque->place = DomHelper::normalizeText($el[2]->textContent);

        $el = $this->find($xpath, '.receipt-header2 .receipt-col1 .value');
        $cheque->number = DomHelper::normalizeText($el[0]->textContent);

        $el = $this->find($xpath, '.receipt-header2 .receipt-col2 .value');
        $date = DomHelper::normalizeText($el[0]->textContent);
        try {
            $cheque->date = new DateTimeImmutable($date);
        } catch (Exception $e) {
            throw new ResponseParseException("Can not parse date: \"$date\"", 0, $e);
        }

        $el = $this->find($xpath, '.receipt-body .items > table .value');
        $cheque->totalAmount = (float)DomHelper::normalizeText($el[0]->textContent);

        $els = iterator_to_array($this->find($xpath, '.receipt-body .items .item'));

        $items = [];
        foreach ($els as $rootEl) {
            /** @var DOMElement $rootEl */

            $titleEl = $this->find($xpath, 'table:nth-child(1) .value', $rootEl);
            if (!$titleEl->length) {
                throw new ParseException('Can not parse item title');
            }
            $name = DomHelper::normalizeText($titleEl[0]->textContent);

            $amountEl = $this->find($xpath, 'table:nth-child(2) .receipt-col2 .value', $rootEl);
            if (!$titleEl->length) {
                throw new ParseException('Can not parse item amount');
            }
            $amount = (float)DomHelper::normalizeText($amountEl[0]->textContent);

            $items[] = new FdoChequeItem($name, $amount);
        }

        $cheque->items = $items;

        return $cheque;
    }

    /**
     * Search elements by CSS selector.
     *
     * @param DOMXPath $xpath
     * @param string $selector
     * @param DOMNode|null $context
     *
     * @return DOMNodeList
     */
    private function find(DOMXPath $xpath, string $selector, ?DOMNode $context = null): DOMNodeList
    {
        $query = $this->selector->toXPath($selector);

        return $xpath->query($query, $context);
    }
}