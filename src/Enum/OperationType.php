<?php

declare(strict_types=1);


namespace Assignment\CommissionTask\Enum;

enum OperationType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';
}
