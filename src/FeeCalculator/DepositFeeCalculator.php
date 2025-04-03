<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\FeeCalculator;

use Assignment\CommissionTask\Entity\Operation;
use Brick\Math\RoundingMode;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;

class DepositFeeCalculator implements CommissionCalculatorInterface
{
    private const FEE_RATE = '0.0003';

    /**
     * @param Operation $operation
     * @return Money
     * @throws UnknownCurrencyException
     */
    public function calculate(Operation $operation): Money
    {
        $feeMoney = $operation->money->multipliedBy(self::FEE_RATE, RoundingMode::UP);
        $scale = $feeMoney->getCurrency()->getDefaultFractionDigits();

        return Money::of($feeMoney->getAmount()->toScale($scale), $feeMoney->getCurrency());
    }
}
