<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\CommissionCalculator;

use Assignment\CommissionTask\Entity\Client;
use Assignment\CommissionTask\Entity\Operation;
use Assignment\CommissionTask\Enum\ClientType;
use Assignment\CommissionTask\Enum\OperationType;
use Assignment\CommissionTask\Exception\CSVParsingException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use DateTimeImmutable;
use Exception;

/**
 * Parses the input csv
 */
class OperationFactory
{
    /**
     * @param array $row
     * @return Operation
     * @throws UnknownCurrencyException
     * @throws Exception
     */
    public function create(array $row): Operation
    {
        If (count($row) != 6) {
            throw new CSVParsingException('Invalid CSV row: insufficient fields.');
        }
        $date = new DateTimeImmutable($row[0]);
        $userId = (int) $row[1];
        $clientType = ClientType::from($row[2]);
        $operationType = OperationType::from($row[3]);
        $money = Money::of($row[4], strtoupper($row[5]));

        $client = new Client($userId, $clientType);

        return new Operation($date, $client, $operationType, $money);
    }
}
