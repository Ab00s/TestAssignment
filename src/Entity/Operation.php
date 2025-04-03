<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\Entity;

use Assignment\CommissionTask\Enum\OperationType;
use Brick\Money\Money;
use DateTimeImmutable;

class Operation
{
    /**
     * @param DateTimeImmutable $date
     * @param Client $client
     * @param OperationType $type
     * @param Money $money
     */
    public function __construct(
        public readonly DateTimeImmutable $date,
        public readonly Client $client,
        public readonly OperationType $type,
        public readonly Money $money
    ) {}
}
