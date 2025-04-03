<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Assignment\CommissionTask\CommissionCalculator\Application;
use Assignment\CommissionTask\CommissionCalculator\OperationProcessor;
use Assignment\CommissionTask\FeeCalculator\CalculatorFactory;
use Assignment\CommissionTask\Presentation\FeePresenter;
use Assignment\CommissionTask\Service\CurrencyConverter;
use Assignment\CommissionTask\Service\ExchangeRateProvider;
use Assignment\CommissionTask\State\WeeklyWithdrawalTracker;
use Dotenv\Dotenv;

$inputFile = $argv[1] ?? null;

if (!$inputFile || !file_exists($inputFile)) {
    fwrite(STDERR, "Usage: php script.php input.csv\n");
    exit(1);
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$exchangeRateProvider = new ExchangeRateProvider();
$currencyConverter = new CurrencyConverter($exchangeRateProvider);
$weeklyWithdrawalTracker = new WeeklyWithdrawalTracker();
$calculatorFactory = new CalculatorFactory($currencyConverter, $weeklyWithdrawalTracker);
$feePresenter = new FeePresenter();
$processor = new OperationProcessor($calculatorFactory);
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = new Application($processor, $feePresenter);
try {
    $app->run($inputFile);
} catch (Exception $e) {
}
