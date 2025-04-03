<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\Entity;

use Assignment\CommissionTask\Enum\ClientType;

class Client
{
    /**
     * @param int $id
     * @param ClientType $type
     */
    public function __construct(
        public readonly int $id,
        public readonly ClientType $type
    ) {}
}
