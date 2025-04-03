<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\Service;

use Assignment\CommissionTask\Exception\MoneyConversionException;
use RuntimeException;

class ExchangeRateProvider
{
    private string $endpoint = 'http://api.exchangeratesapi.io/v1/latest';
    private string $accessKey;
    /** @var array<string, string> */
    private array $rates;

    /**
     * @param string $accessKey Your API access key.
     * @param array<string, string> $rates Optional preloaded rates.
     */
    public function __construct(array $rates = [])
    {
        $this->accessKey =  array_key_exists('EXCHANGE_RATES_API_KEY', $_ENV) ? $_ENV['EXCHANGE_RATES_API_KEY'] : '';
        if ($this->accessKey === false) {
            throw new RuntimeException('EXCHANGE_RATES_API_KEY is not set in environment variables.');
        }
        $this->rates = $rates ?: $this->fetchRates();
    }

    /**
     * Fetches the latest rates from the API. Falls back to default rates if fetching fails.
     *
     * @return array<string, string>
     */
    private function fetchRates(): array
    {
        $url = $this->endpoint . '?access_key=' . urlencode($this->accessKey);
        $response = @file_get_contents($url);
        if ($response === false) {
            return $this->loadDefaultRates();
        }
        $data = json_decode($response, true);
        if ($data === null || !isset($data['rates'])) {
            return $this->loadDefaultRates();
        }
        return array_map('strval', $data['rates']);
    }

    /**
     * Returns the fallback rates.
     *
     * @return array<string, string>
     */
    private function loadDefaultRates(): array
    {
        return [
            'USD' => '1.1497',
            'JPY' => '129.53',
        ];
    }

    /**
     * Returns the exchange rate for the given currency relative to EUR.
     *
     * @param string $currency The target currency.
     * @return string
     * @throws MoneyConversionException
     */
    public function getRateToEUR(string $currency): string
    {
        $currency = strtoupper($currency);
        if ($currency === 'EUR') {
            return '1';
        }
        if (!isset($this->rates[$currency])) {
            throw new MoneyConversionException("Exchange rate for {$currency} not available");
        }
        return $this->rates[$currency];
    }
}
