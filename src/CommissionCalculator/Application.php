<?php

declare(strict_types=1);

namespace Assignment\CommissionTask\CommissionCalculator;


use Assignment\CommissionTask\Presentation\FeePresenter;
use Exception;

class Application
{
    protected OperationProcessor $processor;

    protected FeePresenter $feePresenter;

    /**
     * @param OperationProcessor $processor
     * @param FeePresenter $feePresenter
     */
    public function __construct(OperationProcessor $processor, FeePresenter $feePresenter)
    {
        $this->processor = $processor;
        $this->feePresenter = $feePresenter;
    }

    /**
     * @param string $inputFile
     * @return void
     * @throws Exception
     */
    public function run(string $inputFile): void
    {
        $handle = fopen($inputFile, 'r');

        while (($data = fgetcsv($handle)) !== false) {
            echo   $this->feePresenter->present($this->processor->process($data)) . PHP_EOL;
        }

        fclose($handle);
    }
}
