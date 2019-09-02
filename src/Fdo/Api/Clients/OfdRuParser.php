<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo\Api\Clients;

use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\CssSelector\CssSelectorConverter;
use vvvitaly\txs\Fdo\Api\FdoCheque;
use vvvitaly\txs\Fdo\Api\FdoChequeItem;
use vvvitaly\txs\Libs\Html\DomHelper;
use vvvitaly\txs\Libs\Html\DOMLoadingException;

/**
 * Default parser implementation (parse HTML)
 */
final class OfdRuParser
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
    public function parse(ResponseInterface $response): FdoCheque
    {
        $html = $response->getBody()->getContents();

        $doc = $this->loadDom($html);

        [$chequeAttributes, $items] = $this->collectData($doc);

        foreach (['ДАТА ВЫДАЧИ', 'ФИСКАЛЬНЫЙ ДОКУМЕНТ', 'МЕСТО РАСЧЁТОВ', 'ИТОГ'] as $name) {
            if (!isset($chequeAttributes[$name])) {
                throw new ResponseParseException("Can not parse cheque part \"{$name}\"");
            }
        }

        if (empty($items)) {
            throw new ResponseParseException('Can not parse cheque items');
        }

        $cheque = new FdoCheque();
        try {
            $cheque->date = DateTimeImmutable::createFromFormat('d.m.y H:i', $chequeAttributes['ДАТА ВЫДАЧИ']);
        } catch (Exception $e) {
            throw new ResponseParseException('Can not parse response date', 0, $e);
        }
        $cheque->number = $chequeAttributes['ФИСКАЛЬНЫЙ ДОКУМЕНТ'];
        $cheque->place = $chequeAttributes['МЕСТО РАСЧЁТОВ'];
        $cheque->totalAmount = (float)$chequeAttributes['ИТОГ'];
        $cheque->items = array_map(static function (array $parsedItem) {
            return new FdoChequeItem($parsedItem[0], (float)$parsedItem[1]);
        }, $items);

        return $cheque;
    }

    /**
     * Load HTML into DOMDocument. If date can't be loaded the ResponseParseException is thrown.
     *
     * @param string $html
     *
     * @return DOMDocument
     */
    private function loadDom(string $html): DOMDocument
    {
        try {
            return DomHelper::loadDocument($html);
        } catch (DOMLoadingException $exception) {
            throw new ResponseParseException('Can not load response into DOM document', 0, $exception);
        }
    }

    /**
     * Collect data from the document. Returns array in format:
     * [
     *   [key-value array of cheque attributes],
     *   [list of cheque items [name, amount]]
     * ]
     *
     * @param DOMDocument $document
     *
     * @return array
     */
    private function collectData(DOMDocument $document): array
    {
        $xpath = new DOMXPath($document);

        $keysNodes = $xpath->query($this->selector->toXPath('div.ifw-col.text-left'));
        $valuesNodes = $xpath->query($this->selector->toXPath('div.ifw-col.text-right'));

        $chequeAttributes = [];
        $items = [];
        foreach ($keysNodes as $index => $keyNode) {
            /**  @var DOMElement $keyNode */
            /**  @var DOMElement $valueNode */

            if (!isset($valuesNodes[$index])) {
                continue;
            }
            $valueNode = $valuesNodes[$index];

            $container = $keyNode->parentNode->parentNode ?? null;

            if ($container && strpos($container->getAttribute('class'), 'ifw-bill-item') !== false) { // item
                $item = $this->getTextValue($keyNode);
                $value = $xpath->query($this->selector->toXPath('.text-right div:nth-child(3) span:nth-child(2)'),
                    $container);
                if ($value->length) {
                    $items[] = [$item, $value->item(0)->textContent];
                }
            } else {
                $chequeAttributes[strtoupper($this->getTextValue($keyNode))] = $this->getTextValue($valueNode);
            }
        }

        return [$chequeAttributes, $items];
    }

    /**
     * @param DOMElement $element
     *
     * @return string
     */
    private function getTextValue(DOMElement $element): string
    {
        return DomHelper::normalizeText($element->textContent);
    }
}