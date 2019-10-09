<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser;

/**
 * Information about one step of complex operation
 */
interface OperationStepInterface
{
    /**
     * Check if this step is the last one in operation flow.
     *
     * @return bool
     */
    public function isTerminalStep(): bool;

    /**
     * Check if the specified step and the current steps are related to one operation.
     *
     * @param OperationStepInterface $operationStep
     *
     * @return bool
     */
    public function isRelatedToStep(OperationStepInterface $operationStep): bool;
}