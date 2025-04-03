<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\FeeCalculator;

use Assignment\CommissionTask\Entity\Operation;

interface CalculatorFactoryInterface
{
    /**
     * @param Operation $operation
     * @return CommissionCalculatorInterface
     */
    public function create(Operation $operation): CommissionCalculatorInterface;
}
