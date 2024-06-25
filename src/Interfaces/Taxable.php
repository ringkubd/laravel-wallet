<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Taxable
{
    /**
     * Specify the percentage of the amount. For example, the product costs $100, the equivalent of 15%. That's $115.
     *
     * Minimum 0; Maximum 100 Example: return 7.5; // 7.5%
     */
    public function getFeePercent(): float|int;
    /**
     * Specify the percentage of the deposit amount. For example, you deposit $100, the equivalent of 15%. That's $115 will be credited.
     *
     * Minimum 0; Maximum 100 Example: return 7.5; // 7.5%
     */
    public function getDepositFeePercent(): float|int;

    /**
     * Specify the percentage of the withdrawal amount. For example, the product costs $100, the equivalent of 15%. That's $85 will be withdrawal.
     *
     * Minimum 0; Maximum 100 Example: return 7.5; // 7.5%
     */
    public function getWithdrawFeePercent(): float|int;
}
