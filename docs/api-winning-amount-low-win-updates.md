# LuckyBet API Update: Winning Amount & Low Win Integration

## Overview
This document describes recent updates to the LuckyBet API and backend logic to support flexible payout management, including both standard winning amounts and per-number/per-amount low win overrides. These changes impact both backend logic and API responses, and are important for frontend developers integrating with the system.

---

## Key Changes

### 1. **Winning Amounts Table & Model**
- New `winning_amounts` table and model.
- Associates a default payout (`winning_amount`) with each game type and bet amount.
- Managed via Filament admin panel ("Betting Management" group).

### 2. **Low Win Numbers Table & Model**
- New `low_win_numbers` table and model.
- Allows admins to set reduced payout (low win) rules for specific bet numbers or all numbers, for a given game type and amount.
- Supports both generic (all numbers) and specific (per-number) rules.

### 3. **Bet Model & Locked Payouts**
- **`winning_amount`**: Now STORED in the `bets` table at the time each bet is placed. This value is locked and will never change, even if payout rules are updated later. (For legacy bets, if the column is null, it falls back to config logic.)
- **`is_low_win`**: Boolean, true if a low win rule applies to this bet (based on the config at the time the bet was placed).
- **Why?** This ensures payout fairness, compliance, and auditability. Old bets always pay what was promised at the time of placement.

### 4. **API Response Changes**
- All Bet-related API responses (including `/bets`, `/bets/:id`, `/bets/hits`, etc.) now include:
    - `winning_amount`: The locked-in payout for this bet (from the column), or `null` for legacy bets.
    - `is_low_win`: Boolean, true if a low win rule applies.

#### Example Response (BetResource):
```json
{
  "id": 123,
  "ticket_id": "ABC123XYZ",
  "bet_number": "21",
  "amount": "2",
  "winning_amount": 1500,
  "is_low_win": true,
  ...
}
```

### 5. **N+1 Optimized**
- All queries are eager loaded (`draw`, `draw.result`, `gameType`, etc.) to avoid N+1 issues.

---

## Frontend Integration Notes
- **Always display `winning_amount` to the user** for transparency.
- The `winning_amount` is now guaranteed to be the correct, locked payout for each bet. It will not change, even if admin updates payout rules later.
- If `winning_amount` is `null`, show a warning or prompt to contact admin (this should only happen for legacy bets).
- Use `is_low_win` to visually indicate reduced payout bets.

---

## Migration & Model Details
- See `database/migrations/2025_05_16_205104_create_winning_amounts_table.php` and `2025_05_06_032800_create_low_win_numbers_table.php` for schema.
- See `WinningAmount.php` and `LowWinNumber.php` for model logic.

---

## Contact
For questions or integration support, contact the backend team.

---

**Release Date:** 2025-05-16
