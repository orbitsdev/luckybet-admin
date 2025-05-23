
# Lucky Bet API – Place Bet Logic (Updated for 2025)

## Overview

This update standardizes the business rules and backend logic for placing bets to support risk management, flexible payout, and per-teller commission calculation.

---

## Key Concepts

### 1. Bet Ratio (Cap/Sold Out)
- **What:** Maximum allowed total bet for a specific number in a given draw/game/location.
- **Why:** Prevents operator overexposure on "hot" numbers.
- **Effect:** Once the cap is reached, further bets on that number are rejected with a “Sold Out” error.

### 2. Low Win Number (Override Payout)
- **What:** Per-number override that sets a **lower payout** for a specific number/draw/game/location (e.g., ₱100 per ₱1 bet instead of ₱500).
- **Why:** Allows management to limit risk on numbers that are heavily bet, while still accepting bets.
- **Effect:** If set, the bet’s `winning_amount` uses the low win value; otherwise, the standard payout is used.

### 3. Commission Calculation
- **What:** Each teller has a commission rate (default 15%, or as set by coordinator). 
- **How:** Commission is calculated on bet placement as `commission_amount = amount * commission_rate`.
- **Effect:** Rate/amount are saved in the bet; they don’t change if the teller’s commission changes later.

---

## Place Bet – Business Logic Flow

1. **Validate**: Ensure all required fields (bet number, amount, draw ID, game type, etc.) are present and correct.
2. **Check User Location**: User (teller) must be assigned to a branch/location.
3. **Check Draw Status**: Draw must be open (`is_open = true`).
4. **Bet Ratio (Cap) Logic**:
    - **Sum** total `amount` of all bets for the same draw, game, location, and number.
    - **Retrieve cap** from `bet_ratios` table.
    - **If cap would be exceeded** by this bet, **reject** with "Sold Out".
5. **Low Win Number Override**:
    - **Look up** in `low_winning_numbers` for an override on this draw/game/location/number.
    - **If found**, use its `winning_amount` as the payout.
    - **If not**, use default from `winning_amounts`.
6. **Commission Calculation**:
    - **Retrieve** the teller’s commission rate (default 15%).
    - **Calculate**: `commission_amount = amount * commission_rate`.
    - **Store** both `commission_rate` and `commission_amount` in the bet.
7. **Create Bet**:
    - Save all fields, including bet number, amount, winning amount, commission fields, and all references.
    - Generate unique `ticket_id` for QR code.
8. **Commit Transaction**.
9. **Return Success** (with bet details).

---

## Example – Updated placeBet Code (Laravel-style Pseudocode)

```php
public function placeBet(Request $request)
{
    $data = $request->validate([
        'bet_number' => 'required|string|max:5',
        'amount' => 'required|numeric|min:1',
        'draw_id' => 'required|exists:draws,id',
        'game_type_id' => 'required|exists:game_types,id',
        'customer_id' => 'nullable|exists:users,id',
        'is_combination' => 'boolean',
        'd4_sub_selection' => 'nullable|in:S2,S3'
    ]);

    $user = $request->user();
    if (!$user->location_id) {
        return ApiResponse::error('User does not have a location assigned', 422);
    }

    try {
        $draw = Draw::findOrFail($data['draw_id']);
        if (!$draw->is_open) {
            return ApiResponse::error('This draw is no longer accepting bets', 422);
        }

        DB::beginTransaction();

        // 1. BET RATIO (CAP/SOLD OUT) CHECK
        $totalBetForNumber = Bet::where('draw_id', $data['draw_id'])
            ->where('game_type_id', $data['game_type_id'])
            ->where('location_id', $user->location_id)
            ->where('bet_number', $data['bet_number'])
            ->sum('amount');

        $cap = BetRatio::where('draw_id', $data['draw_id'])
            ->where('game_type_id', $data['game_type_id'])
            ->where('location_id', $user->location_id)
            ->where('bet_number', $data['bet_number'])
            ->value('max_amount');

        if ($cap !== null && ($totalBetForNumber + $data['amount']) > $cap) {
            DB::rollBack();
            return ApiResponse::error('Sold Out', 422);
        }

        // 2. LOW WIN OVERRIDE / WINNING AMOUNT LOGIC
        $lowWin = LowWinNumber::where('draw_id', $data['draw_id'])
            ->where('game_type_id', $data['game_type_id'])
            ->where('location_id', $user->location_id)
            ->where('bet_number', $data['bet_number'])
            ->first();

        $winningAmount = $lowWin
            ? $lowWin->winning_amount
            : (WinningAmount::where('game_type_id', $data['game_type_id'])
                ->where('location_id', $user->location_id)
                ->where('amount', $data['amount'])
                ->value('winning_amount'));

        if (is_null($winningAmount)) {
            DB::rollBack();
            return ApiResponse::error(
                'Winning amount is not set for this game type and amount. Please contact admin.',
                422
            );
        }

        // 3. COMMISSION LOGIC
        $commissionRate = $user->commission->rate ?? 0.15; // default 15% if not set
        $commissionAmount = $data['amount'] * $commissionRate;

        $bet = Bet::create([
            'bet_number'       => $data['bet_number'],
            'amount'           => $data['amount'],
            'winning_amount'   => $winningAmount,
            'draw_id'          => $data['draw_id'],
            'game_type_id'     => $data['game_type_id'],
            'teller_id'        => $user->id,
            'customer_id'      => $data['customer_id'] ?? null,
            'location_id'      => $user->location_id,
            'bet_date'         => today(),
            'ticket_id'        => strtoupper(Str::random(6)),
            'is_combination'   => $data['is_combination'] ?? false,
            'd4_sub_selection' => $data['d4_sub_selection'] ?? null,
            'commission_rate'  => $commissionRate,
            'commission_amount'=> $commissionAmount,
        ]);

        DB::commit();

        $bet->load(['draw', 'customer', 'location', 'gameType']);

        return ApiResponse::success(new BetResource($bet), 'Bet placed successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error('Failed to place bet: ' . $e->getMessage(), 500);
    }
}
```

---

## Required Table Fields (for Bets Table)

- `id`
- `draw_id`
- `game_type_id`
- `teller_id`
- `customer_id`
- `location_id`
- `bet_number`
- `amount`
- `winning_amount`
- `commission_rate`
- `commission_amount`
- `bet_date`
- `ticket_id`
- `is_combination`
- `d4_sub_selection`
- `created_at`, `updated_at`

---

## Notes for Backend Developer

- **ALWAYS** check bet ratio (cap/sold out) before placing bet.
- **ALWAYS** check low win override before using default payout.
- **ALWAYS** store teller’s commission rate and amount **at the time of bet** (do not update after).
- **ALWAYS** include location/branch in all queries for risk logic.
- **Fail fast** with clear errors for cap, payout, or config issues.

---

## Ready for Integration

- This logic is ready for multi-branch, multi-draw, per-number risk control, and per-teller commission.
- All logic is compatible with both current STL Lucky Bet reporting and system requirements.

