<?php

namespace App\Services;

class ClaimHelper
{
    public static function calculateCommission(float $winningAmount, float $rate): float
    {
        return ($winningAmount * $rate) / 100;
    }
}
