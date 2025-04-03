<?php
declare(strict_types=1);

namespace Assignment\CommissionTask\State;

use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use DateTimeImmutable;

class WeeklyWithdrawalTracker
{
    /**
     * Stores state per client and week.
     * Key format: "{clientId}:{year-week}"
     * Each value is an array with:
     *   - count: int, number of withdrawals this week
     *   - used: Money, cumulative free amount used (in EUR)
     *
     * @var array<string, array{count: int, used: Money}>
     */
    private array $state = [];

    /**
     * @param int $clientId
     * @param DateTimeImmutable $date
     * @return array
     * @throws UnknownCurrencyException
     */
    public function getState(int $clientId, DateTimeImmutable $date): array
    {
        $key = $this->getKey($clientId, $date);
        if (!isset($this->state[$key])) {
            $this->state[$key] = ['count' => 0, 'used' => Money::of('0', 'EUR')];
        }
        return $this->state[$key];
    }

    /**
     * @param int $clientId
     * @param DateTimeImmutable $date
     * @return string
     */
    private function getKey(int $clientId, DateTimeImmutable $date): string
    {
        return $clientId . ':' . $date->format('o-W');
    }

    /**
     * @param int $clientId
     * @param DateTimeImmutable $date
     * @param Money $freeUsed
     * @return void
     * @throws MoneyMismatchException
     * @throws UnknownCurrencyException
     */
    public function recordFreeUsage(int $clientId, DateTimeImmutable $date, Money $freeUsed): void
    {
        $key = $this->getKey($clientId, $date);
        if (!isset($this->state[$key])) {
            $this->state[$key] = ['count' => 0, 'used' => Money::of('0', 'EUR')];
        }
        $this->state[$key]['used'] = $this->state[$key]['used']->plus($freeUsed);
    }

    /**
     * @param int $clientId
     * @param DateTimeImmutable $date
     * @return void
     * @throws UnknownCurrencyException
     */
    public function incrementCount(int $clientId, DateTimeImmutable $date): void
    {
        $key = $this->getKey($clientId, $date);
        if (!isset($this->state[$key])) {
            $this->state[$key] = ['count' => 0, 'used' => Money::of('0', 'EUR')];
        }
        $this->state[$key]['count']++;
    }
}
