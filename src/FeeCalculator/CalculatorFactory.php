<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\FeeCalculator;

use Assignment\CommissionTask\Entity\Operation;
use Assignment\CommissionTask\Enum\ClientType;
use Assignment\CommissionTask\Enum\OperationType;
use Assignment\CommissionTask\Exception\OperationNotSupportedException;
use Assignment\CommissionTask\Service\CurrencyConverter;
use Assignment\CommissionTask\State\WeeklyWithdrawalTracker;

class CalculatorFactory implements CalculatorFactoryInterface
{
    /**
     * @param CurrencyConverter $currencyConverter
     * @param WeeklyWithdrawalTracker $weeklyWithdrawalTracker
     */
    public function __construct(
        private readonly CurrencyConverter $currencyConverter,
        private readonly WeeklyWithdrawalTracker $weeklyWithdrawalTracker
    ) {}

    /**
     * @param Operation $operation
     * @return CommissionCalculatorInterface
     */
    public function create(Operation $operation): CommissionCalculatorInterface
    {
        return match (true) {
            $operation->type === OperationType::DEPOSIT =>
            new DepositFeeCalculator(),

            $operation->type === OperationType::WITHDRAW && $operation->client->type === ClientType::PRIVATE =>
            new PrivateWithdrawFeeCalculator($this->currencyConverter, $this->weeklyWithdrawalTracker),

            $operation->type === OperationType::WITHDRAW && $operation->client->type === ClientType::BUSINESS =>
            new BusinessWithdrawFeeCalculator(),

            default => throw new OperationNotSupportedException('Unsupported operation type: ' . $operation->type->value),
        };
    }
}
