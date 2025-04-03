<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\CommissionCalculator;

use Assignment\CommissionTask\FeeCalculator\CalculatorFactoryInterface;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;

class OperationProcessor
{
    /**
     * @param CalculatorFactoryInterface $factory
     */
    public function __construct(
        private readonly CalculatorFactoryInterface $factory,
    ) {
    }

    /**
     * @param array $row
     * @return Money
     * @throws UnknownCurrencyException
     */
    public function process(array $row): Money
    {
        $operation = (new OperationFactory())->create($row);
        $calculator = $this->factory->create($operation);
        return $calculator->calculate($operation);
    }
}
