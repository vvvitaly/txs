<?php

declare(strict_types=1);

namespace App\Fdo\Api\Clients;

use App\Fdo\Api\FdoCheque;
use App\Fdo\Api\FdoChequeItem;
use App\Libs\Html\DomHelper;
use App\Libs\Html\DOMLoadingException;
use DateTimeImmutable;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\CssSelector\CssSelectorConverter;

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
     * @inheritDoc
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

        $els = iterator_to_array($this->find($xpath, '.receipt-body .items .value'));

        $last = array_pop($els);
        $cheque->totalAmount = (float)DomHelper::normalizeText($last->textContent);

        $items = [];
        foreach (array_chunk($els, 6) as $chunk) {
            /** @var DOMElement[] $chunk */

            $name = DomHelper::normalizeText($chunk[0]->textContent);
            $amount = (float)DomHelper::normalizeText($chunk[3]->textContent);
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