<?php
declare(strict_types=1);

namespace Assignment\CommissionTask\Presentation;

use Brick\Money\Money;

class FeePresenter
{
    /**
     * @param Money $money
     * @return string
     */
    public function present(Money $money): string
    {
        $scale = $money->getCurrency()->getDefaultFractionDigits();
        return $money->getAmount()->toScale($scale)->__toString();
    }
}
