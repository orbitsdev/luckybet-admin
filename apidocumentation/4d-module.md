# 4D Module Documentation

## What is this?
The 4D Module is an advanced betting feature that allows customers to place bets on 4-digit numbers with support for S2 and S3 subtypes and combination betting. It is designed to reflect real-world betting scenarios, enabling flexible bet structuring, result calculation, and reporting.

---

## Real-World Scenario

**Example:**
A customer wants to bet on the 4D number "1234" for the 9 PM draw. They select the S2 subtype and enable combination betting. The system generates all valid 2-digit combinations from "1234" (e.g., "12", "23", "34"). The customer specifies an amount for each combination, such as ₱5 for "12" and ₱5 for "34".

- Each combination is treated as an individual S2 bet, but all are grouped under a single ticket for reporting and customer reference.
- If any combination wins in the S2 result, the customer receives the S2 prize for that combination.

---

## How it Works

### Placing a Bet
- The customer submits a 4D number, selects a subtype (S2 or S3), and enables combination betting.
- The frontend sends a payload like:
```json
{
  "bet_number": "1234",
  "draw_id": 1,
  "game_type_id": 2,
  "is_combination": true,
  "d4_sub_selection": "S2",
  "combinations": [
    { "combination": "12", "amount": 5 },
    { "combination": "34", "amount": 5 }
  ]
}
```

### Backend Processing
- The API validates the request and creates a parent bet record for the main 4D number.
- For each combination, a child bet record is created, referencing the parent.
- All combinations share the same ticket number for easy tracking.

### Result Calculation
- Bets with an S2/S3 subtype are checked only against S2/S3 results.
- Standard 4D bets (no subtype) are checked against the 4D result.
- No bet is eligible for both 4D and S2/S3 prizes at the same time.

### Reporting
- Reports and API responses group all combinations under their parent bet.
- This structure supports clear customer receipts and detailed admin reporting.

---

## Data Model Changes

### bets table
- Add a `parent_id` column (nullable, foreign key to `bets.id`).
- All combination bets reference their parent bet.

### Bet model
Add relationships:
```php
public function combinations() {
    return $this->hasMany(Bet::class, 'parent_id');
}
public function parent() {
    return $this->belongsTo(Bet::class, 'parent_id');
}
```

### API Changes (placeBet)
- Accepts a `combinations` array in the request when `is_combination` and `d4_sub_selection` are set.
- Creates a parent bet for the main 4D number.
- Creates a child bet for each combination, referencing the parent and storing its own amount.
- All bets (parent and children) share the same ticket ID.

### Eloquent Load/Relations
- When loading a bet, use `$bet->load(['combinations', 'draw', 'customer', 'location', 'gameType'])` to include all related data for reporting or API response.
- Reports and API endpoints should display the parent bet with its child combinations for clarity.

---

## Example (Real-World)
Suppose a customer places the following bet:
- 4D Number: **5678**
- Subtype: **S2**
- Combinations: **"56" (₱10), "67" (₱10), "78" (₱10)**
- Draw: **9 PM**

**Database Records:**

| id  | bet_number | d4_sub_selection | parent_id | amount | ticket_id | draw_id | ... |
|-----|------------|------------------|-----------|--------|-----------|---------|-----|
| 100 | 5678       | S2               | null      | 0      | XYZ123    | 12      | ... |
| 101 | 56         | S2               | 100       | 10     | XYZ123    | 12      | ... |
| 102 | 67         | S2               | 100       | 10     | XYZ123    | 12      | ... |
| 103 | 78         | S2               | 100       | 10     | XYZ123    | 12      | ... |

- The parent bet (id 100) stores the original 4D number and overall ticket info. Amount is 0/null if only combos are played.
- Each child bet stores the S2 combo in `bet_number`, references the parent via `parent_id`, and has its own amount.
- All bets (parent and children) include draw_id, ticket_id, and other relevant fields.

**Result Calculation:**
- For straight D4 bets (no combos), check the parent bet only.
- For S2/S3 combos, check the child bets only (not the parent).
- No bet is eligible for both D4 and S2/S3 prizes at the same time.

---
 
## Summary Table
| Bet Type           | d4_sub_selection | Checked Against | Eligible Prize | Appears In Report As |
|--------------------|------------------|-----------------|---------------|---------------------|
| Standard 4D        | null             | 4D result       | 4D prize      | 4D                  |
| S2 Combination Bet | 'S2'             | S2 result       | S2 prize      | D4-S2               |
| S3 Combination Bet | 'S3'             | S3 result       | S3 prize      | D4-S3               |

---

## Additional Notes
- Only the parent bet should have `is_combination=true`; child bets inherit this context and do not need the field.
- All bets (parent and children) should have `draw_id`, `ticket_id`, and other key fields for traceability and reporting.
- Amount on the parent is 0/null if only combos are played; otherwise, it reflects the straight D4 amount.
- Parent-child structure is preferred over a flat structure because it allows for better grouping, reporting, and matches real-world ticketing and receipt scenarios.

---

## Summary
- The 4D Module supports advanced betting scenarios for S2/S3 subtypes and combinations.
- Bets are grouped using a parent-child relationship, enabling flexible reporting and real-world ticketing.
- The system ensures correct result calculation and clear separation between standard 4D and S2/S3 bets.
- The API and Eloquent load logic are updated to support this structure for both backend processing and frontend display.

---

**End of Documentation**
