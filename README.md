# Commission Fee Calculator

This project is a PHP application that calculates commission fees for deposits and withdrawals. It processes operations
from a CSV file, supports multiple currencies with live exchange rates.

## Features

- **CSV Processing:** Reads operations from a CSV file and outputs commission fees.
- **Money Arithmetic:** Uses [Brick\Money](https://github.com/brick/money) for high-precision, immutable monetary
  calculations.
- **Live Exchange Rates:** Retrieves live exchange rates
  from [exchangeratesapi.io](https://exchangeratesapi.io/documentation/) with a fallback to default hardcoded rates.
- **Environment Variables:** Uses [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) to securely manage API keys
  and other sensitive configuration.
- **Custom Exceptions:** Implements domain-specific exception classes for better error handling.
- **Unit Testing:** Comprehensive tests are written using PHPUnit with data providers to cover valid and invalid CSV
  scenarios.

## Installation

1. Clone the repository.
2. Run the following command in the project root to install dependencies:

```bash
composer install
```

## Configuration

* Edit the .env file and add your own API key into it.

## Running the Application

```bash
php script.php path/to/input.csv
```

## Running Tests

To run the test suite using PHPUnit, execute:

```bash
composer run test
```

