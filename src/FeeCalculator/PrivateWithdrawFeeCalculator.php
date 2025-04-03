<?php
declare(strict_types=1);

namespace Assignment\CommissionTask\FeeCalculator;

use Assignment\CommissionTask\Entity\Operation;
use Assignment\CommissionTask\Service\CurrencyConverter;
use Assignment\CommissionTask\State\WeeklyWithdrawalTracker;
use Brick\Math\RoundingMode;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;

class PrivateWithdrawFeeCalculator implements CommissionCalculatorInterface
{
    private const FEE_RATE = '0.003';
    private const WEEKLY_FREE_AMOUNT_EUR = '1000';
    private const MAX_FREE_OPERATIONS = 3;

    /**
     * @param CurrencyConverter $currencyConverter
     * @param WeeklyWithdrawalTracker $tracker
     */
    public function __construct(
        private readonly CurrencyConverter $currencyConverter,
        private readonly WeeklyWithdrawalTracker $tracker
    ) {}

    /**
     * @param Operation $operation
     * @return Money
     * @throws MoneyMismatchException
     * @throws UnknownCurrencyException
     */
    public function calculate(Operation $operation): Money
    {
        $clientId = $operation->client->id;
        $date = $operation->date;
        $state = $this->tracker->getState($clientId, $date);

        $amountEur = $this->currencyConverter->convertToEUR($operation->money);

        $freeLimit = Money::of(self::WEEKLY_FREE_AMOUNT_EUR, 'EUR');

        $feeBase = $operation->money;

        if ($state['count'] < self::MAX_FREE_OPERATIONS) {
            $this->tracker->incrementCount($clientId, $date);

            if ($state['used']->isLessThan($freeLimit)) {
                $remainingFree = $freeLimit->minus($state['used']);

                $freeUsed = $amountEur->isGreaterThan($remainingFree)
                    ? $remainingFree
                    : $amountEur;

                $this->tracker->recordFreeUsage($clientId, $date, $freeUsed);
                $chargeableEur = $amountEur->minus($freeUsed);

                if ($chargeableEur->isZero()) {
                    $feeBase = Money::of('0', $operation->money->getCurrency()->getCurrencyCode());
                } else {
                    $feeBase = $this->currencyConverter->convertFromEUR($chargeableEur, $operation->money->getCurrency()->getCurrencyCode());
                }
            }
        }

        $fee = $feeBase->multipliedBy(self::FEE_RATE, RoundingMode::UP);
        $scale = $fee->getCurrency()->getDefaultFractionDigits();

        return Money::of($fee->getAmount()->toScale($scale), $fee->getCurrency());
    }
}
