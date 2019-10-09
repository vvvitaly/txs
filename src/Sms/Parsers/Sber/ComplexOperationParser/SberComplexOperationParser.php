<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser;

use SplObjectStorage;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

/**
 * Parse bill from several messages. It's useful when SMS doesn't contain ALL necessary information, but this SMS is a
 * part of some flow, and information could be parsed from other messages of this flow (e.g. transfer message with
 * confirmation).
 */
final class SberComplexOperationParser implements MessageParserInterface
{
    /**
     * @var OperationStepParserInterface
     */
    private $stepParser;

    /**
     * @var BillsFactoryInterface
     */
    private $billsFactory;

    /**
     * @var OperationStepInterface[]
     */
    private $operationsSteps;

    /**
     * @param OperationStepParserInterface $stepParser
     * @param BillsFactoryInterface $billsFactory
     */
    public function __construct(
        OperationStepParserInterface $stepParser,
        BillsFactoryInterface $billsFactory
    ) {
        $this->stepParser = $stepParser;
        $this->billsFactory = $billsFactory;
        $this->operationsSteps = new SplObjectStorage();
    }

    /**
     * @inheritDoc
     */
    public function parse(Message $sms): ?Bill
    {
        $currentStep = $this->stepParser->parseStep($sms);
        if (!$currentStep) {
            return null;
        }

        $firstStep = $currentStep;
        foreach ($this->operationsSteps as $step) {
            if ($currentStep->isRelatedToStep($step)) {
                $firstStep = $step;
                break;
            }
        }

        $parsedSteps = [];
        if ($firstStep !== null && $this->operationsSteps->contains($firstStep)) {
            $parsedSteps = $this->operationsSteps[$firstStep];
        }
        $parsedSteps[] = $currentStep;
        $this->operationsSteps[$firstStep] = $parsedSteps;

        if (!$currentStep->isTerminalStep()) {
            return null;
        }

        try {
            return $this->billsFactory->createBill(...$parsedSteps);
        } catch (CanNotCreateBillException $exception) {
        }

        return null;
    }
}