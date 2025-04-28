<?php

namespace App\Services;

use App\Models\Result;
use Illuminate\Support\Str;

class BetHelper
{
    public static function generateTicketId(): string
    {
        return strtoupper(Str::random(10));
    }

    public static function isWon(string $betNumber, Result $result): bool
    {
        return $betNumber === $result->winning_number;
    }
}
