<?php

declare(strict_types=1);


namespace Assignment\CommissionTask\Enum;

enum ClientType: string
{
    case PRIVATE = 'private';
    case BUSINESS = 'business';
}
