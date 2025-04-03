<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\Tests\CommissionCalculator;

use Assignment\CommissionTask\CommissionCalculator\Application;
use Assignment\CommissionTask\CommissionCalculator\OperationProcessor;
use Assignment\CommissionTask\Exception\CSVParsingException;
use Assignment\CommissionTask\FeeCalculator\CalculatorFactory;
use Assignment\CommissionTask\Presentation\FeePresenter;
use Assignment\CommissionTask\Service\CurrencyConverter;
use Assignment\CommissionTask\Service\ExchangeRateProvider;
use Assignment\CommissionTask\State\WeeklyWithdrawalTracker;
use Exception;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{

    /**
     * @param array $csvRows
     * @param array $expectedFees
     * @return void
     * @throws Exception
     *
     * @dataProvider commissionProvider
     */
    public function testCommissionCalculation(array $csvRows, array $expectedFees): void
    {
        $exchangeRates = [
            'USD' => '1.1497',
            'JPY' => '129.53',
        ];

        $exchangeProvider = new ExchangeRateProvider($exchangeRates);
        $converter = new CurrencyConverter($exchangeProvider);
        $tracker = new WeeklyWithdrawalTracker();
        $factory = new CalculatorFactory($converter, $tracker);
        $feePresenter = new FeePresenter();
        $processor = new OperationProcessor($factory);
        $application = new Application($processor, $feePresenter);

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tmpFile, implode(PHP_EOL, $csvRows));

        ob_start();
        $application->run($tmpFile);
        $output = trim(ob_get_clean());

        unlink($tmpFile);

        $actualFees = explode(PHP_EOL, $output);
        $this->assertSame($expectedFees, $actualFees);
    }

    /**
     * Test an invalid CSV row missing a field.
     * @throws CSVParsingException|Exception
     */
    public function testInvalidCSVMissingFields(): void
    {
        $invalidRows = [
            // Missing currency field.
            '2016-03-14,1,private,withdraw,400.00'
        ];
        $tmpFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tmpFile, implode(PHP_EOL, $invalidRows));

        $exchangeRates = [
            'USD' => '1.1497',
            'JPY' => '129.53',
        ];
        $exchangeProvider = new ExchangeRateProvider($exchangeRates);
        $converter         = new CurrencyConverter($exchangeProvider);
        $tracker           = new WeeklyWithdrawalTracker();
        $factory           = new CalculatorFactory($converter, $tracker);
        $feePresenter      = new FeePresenter();
        $processor         = new OperationProcessor($factory);
        $application       = new Application($processor, $feePresenter);

        $this->expectException(CSVParsingException::class);
        $application->run($tmpFile);
        unlink($tmpFile);
    }

    /**
     * Test an invalid CSV row with an invalid date.
     */
    public function testInvalidCSVInvalidDate(): void
    {
        $invalidRows = [
            'invalid-date,1,private,withdraw,400.00,EUR'
        ];
        $tmpFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tmpFile, implode(PHP_EOL, $invalidRows));

        $exchangeRates = [
            'USD' => '1.1497',
            'JPY' => '129.53',
        ];
        $exchangeProvider = new ExchangeRateProvider($exchangeRates);
        $converter         = new CurrencyConverter($exchangeProvider);
        $tracker           = new WeeklyWithdrawalTracker();
        $factory           = new CalculatorFactory($converter, $tracker);
        $feePresenter      = new FeePresenter();
        $processor         = new OperationProcessor($factory, $feePresenter);
        $application       = new Application($processor, $feePresenter);

        $this->expectException(Exception::class);
        $application->run($tmpFile);
        unlink($tmpFile);
    }

    public function commissionProvider(): array
    {
        return [
            'Weekly Limit Edge Case' => [
                [
                    '2016-03-14,1,private,withdraw,400.00,EUR',
                    '2016-03-15,1,private,withdraw,400.00,EUR',
                    '2016-03-16,1,private,withdraw,300.00,EUR',
                    '2016-03-21,1,private,withdraw,1100.00,EUR',
                    '2016-03-22,1,private,withdraw,10.00,EUR',
                ],
                ['0.00', '0.00', '0.30', '0.30', '0.03'],
            ],
            'Currency Conversion (JPY & USD)' => [
                [
                    '2016-04-04,2,private,withdraw,500.00,EUR',
                    '2016-04-05,2,private,withdraw,60000,JPY',
                    '2016-04-06,2,private,withdraw,200.00,USD',
                    '2016-04-07,2,private,withdraw,100.00,EUR',
                ],
                ['0.00', '0', '0.48', '0.30'],
            ],
            'Mixed Clients and Operations' => [
                [
                    '2016-05-02,3,business,withdraw,1000.00,EUR',
                    '2016-05-03,4,private,withdraw,700.00,EUR',
                    '2016-05-03,4,private,withdraw,500.00,EUR',
                    '2016-05-04,3,business,deposit,20000.00,EUR',
                    '2016-05-05,4,private,withdraw,100.00,EUR',
                    '2016-05-05,4,private,withdraw,300.00,EUR',
                ],
                ['5.00', '0.00', '0.60', '6.00', '0.30', '0.90'],
            ],
            'Cross-week Edge Case' => [
                [
                    '2016-06-05,5,private,withdraw,500.00,EUR',
                    '2016-06-06,5,private,withdraw,700.00,EUR',
                    '2016-06-07,5,private,withdraw,400.00,EUR',
                    '2016-06-07,5,private,withdraw,100.00,EUR',
                ],
                ['0.00', '0.00', '0.30', '0.30'],
            ],
            'Multiple Small Withdrawals' => [
                [
                    '2016-07-11,6,private,withdraw,200.00,EUR',
                    '2016-07-12,6,private,withdraw,200.00,EUR',
                    '2016-07-12,6,private,withdraw,200.00,EUR',
                    '2016-07-13,6,private,withdraw,200.00,EUR',
                    '2016-07-14,6,private,withdraw,200.00,EUR',
                    '2016-07-15,6,private,withdraw,200.00,EUR',
                ],
                ['0.00', '0.00', '0.00', '0.60', '0.60', '0.60'],
            ],
        ];
    }
}
