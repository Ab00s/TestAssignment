<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\FeeCalculator;

use Assignment\CommissionTask\Entity\Operation;
use Brick\Money\Money;

interface CommissionCalculatorInterface
{
    /**
     * @param Operation $operation
     * @return Money
     */
    public function calculate(Operation $operation): Money;
}
