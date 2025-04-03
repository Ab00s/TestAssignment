<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\Service;

use Brick\Math\RoundingMode;
use Brick\Money\Currency;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;

class CurrencyConverter
{
    public function __construct(
        private readonly ExchangeRateProvider $rateProvider
    ) {}

    /**
     * Converts a Money object to EUR.
     * @throws UnknownCurrencyException
     */
    public function convertToEUR(Money $money): Money
    {
        if ($money->getCurrency()->getCurrencyCode() === 'EUR') {
            return $money;
        }

        $rate = $this->rateProvider->getRateToEUR($money->getCurrency()->getCurrencyCode());
        $converted = $money->dividedBy($rate, RoundingMode::UP);
        return Money::of($converted->getAmount()->__toString(), 'EUR');
    }

    /**
     * Converts a Money object from EUR to the target currency.
     * @throws UnknownCurrencyException
     */
    public function convertFromEUR(Money $money, string $targetCurrency): Money
    {
        $targetCurr = strtoupper($targetCurrency);
        if ($targetCurr === 'EUR') {
            return $money;
        }

        $rate = $this->rateProvider->getRateToEUR($targetCurr);
        $converted = $money->multipliedBy($rate, RoundingMode::UP);
        $defaultScale = Currency::of($targetCurr)->getDefaultFractionDigits();
        $roundedAmount = $converted->getAmount()->toScale($defaultScale, RoundingMode::UP);
        return Money::of($roundedAmount->__toString(), $targetCurr);
    }
}
