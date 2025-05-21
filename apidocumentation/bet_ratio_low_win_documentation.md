
# Bet Ratio (Bet Cap/Sold Out) & Low Win Number Logic

## Purpose & Rationale

### Bet Ratio (Bet Cap/Sold Out)
- **Purpose:** Prevent excessive risk exposure by limiting the total amount that can be bet on any single number per draw and game type.
- **Why Needed:**
    - If too much money is wagered on a “hot” number, and it wins, the operator could lose significant funds.
    - STL and similar games use this as a core risk management mechanism.
- **User Impact:**
    - When the cap for a number is reached, no further bets are allowed for that number and draw (“Sold Out” status is shown to the user).

### Low Win Number
- **Purpose:** Allow the admin to override the standard payout for a specific number in a specific draw/game type, reducing the risk without completely blocking bets on that number.
- **Why Needed:**
    - There are cases where the operator wants to keep betting open for a hot number but pay out less if that number wins.
    - This provides flexible risk management without harming user experience as much as blocking bets would.
- **User Impact:**
    - Bets on a “low win” number will receive a lower payout if that number wins, as set by the admin for that draw/game type.

## Backend Integration & Business Logic

### Required Database Tables
- **bet_ratios:**
    - Holds cap for each bet number per draw and game type.
- **low_win_numbers:**
    - Holds “low win” status and override payout for each number per draw/game type.

### placeBet() Logic Flow

1. **Draw Status Check**
    - Ensure the draw is still open.
2. **Bet Ratio/Cap Check**
    - Before placing a bet, sum all existing bets for the given draw, game type, and number.
    - Retrieve the cap from `bet_ratios`.
    - If (total bet on this number + new bet amount) **exceeds the cap**, **reject the bet** and show “Sold Out.”
3. **Low Win Number Check**
    - After passing the cap check, check `low_win_numbers` for a low win override for the draw, game type, and number.
    - If found, use its `winning_amount` for the bet.
    - If not found, get the standard payout from the default payout table (e.g., `winning_amounts`).
4. **Store the Winning Amount in the Bet Record**
    - This ensures that even if payout rules change after the bet is placed, the user’s payout is locked.

## Pseudocode for placeBet()

```php
// Step 1: Draw open check (already exists)

// Step 2: Bet Ratio/Cap Check
$totalBetForNumber = Bet::where('draw_id', $draw_id)
    ->where('game_type_id', $game_type_id)
    ->where('bet_number', $bet_number)
    ->sum('amount');
$cap = BetRatio::where('draw_id', $draw_id)
    ->where('game_type_id', $game_type_id)
    ->where('bet_number', $bet_number)
    ->value('max_amount');
if ($cap !== null && ($totalBetForNumber + $amount) > $cap) {
    return error('Sold Out');
}

// Step 3: Low Win Number Check
$lowWin = LowWinNumber::where('draw_id', $draw_id)
    ->where('game_type_id', $game_type_id)
    ->where('bet_number', $bet_number)
    ->first();

$winningAmount = $lowWin
    ? $lowWin->winning_amount
    : WinningAmount::where('game_type_id', $game_type_id)
        ->where('amount', $amount)
        ->value('winning_amount');

// Step 4: Save Bet (with calculated winning amount)
Bet::create([...]);
```

## Key Points for Developers

- **Bet Ratio check must happen before placing the bet**—block bets that would exceed the cap.
- **Low Win Number check happens after cap check**—if exists, override standard payout.
- **The process is per-draw, per-game-type, per-bet-number.**
- **Both checks are required on bet placement, not just on payout/claim.**
- **Store the actual winning amount in the bet at creation for audit and integrity.**
- **API should clearly communicate “Sold Out” or “Low Win” situations to the frontend.**

## Benefits
- Reduces risk for the operator.
- Keeps the system fair and transparent.
- Flexible: Admins can manage risk in real time without disabling betting entirely on hot numbers.

---

*Generated: 2025-05-21 08:38:55*
