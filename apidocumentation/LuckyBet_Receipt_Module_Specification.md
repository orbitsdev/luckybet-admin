# LuckyBet Receipt (Cart) Module — Technical Specification

## Purpose
Implement a receipt-based cart workflow so tellers can group multiple bets into a single receipt, save paper, and provide a professional, audit-friendly betting experience.

---

## 1. Database Migration: `receipts` Table

```php
Schema::create('receipts', function (Blueprint $table) {
    $table->id();
    $table->string('ticket_id')->unique()->nullable(); // Assigned on finalization
    $table->unsignedBigInteger('teller_id');
    $table->unsignedBigInteger('location_id');
    $table->date('receipt_date')->nullable(); // Set on finalization
    $table->enum('status', ['draft', 'placed', 'cancelled'])->default('draft');
    $table->decimal('total_amount', 12, 2)->nullable(); // Optionally store, can also compute
    $table->timestamps();

    $table->foreign('teller_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
});
```

---

## 2. Database Migration: Add `receipt_id` to `bets` Table

```php
Schema::table('bets', function (Blueprint $table) {
    $table->unsignedBigInteger('receipt_id')->nullable()->after('id');
    $table->foreign('receipt_id')->references('id')->on('receipts')->onDelete('set null');
});
```

---

## 3. Model Relationships

**Receipt.php**
```php
public function bets()    { return $this->hasMany(Bet::class); }
public function teller()  { return $this->belongsTo(User::class, 'teller_id'); }
public function location(){ return $this->belongsTo(Location::class, 'location_id'); }
```
**Bet.php**
```php
public function receipt() { return $this->belongsTo(Receipt::class); }
```

---

## 4. API Endpoints

| Action           | Method | Endpoint                                | Purpose/Notes                                       |
|------------------|--------|-----------------------------------------|-----------------------------------------------------|
| Get draft        | GET    | `/api/receipts/draft`                   | Fetch or create current teller's draft receipt      |
| Add bet          | POST   | `/api/receipts/{receipt}/bets`          | Validate and add bet to draft                       |
| Remove bet       | DELETE | `/api/receipts/{receipt}/bets/{bet}`    | Remove bet from draft                               |
| Update bet (opt) | PUT    | `/api/receipts/{receipt}/bets/{bet}`    | Edit bet in draft (if needed)                       |
| Finalize receipt | POST   | `/api/receipts/{receipt}/place`         | Place/finalize all bets in receipt, generate ticket |
| Cancel draft     | POST   | `/api/receipts/{receipt}/cancel`        | Cancel/discard draft                                |
| Get receipt      | GET    | `/api/receipts/{receipt}`               | Full details (for review/print)                     |
| List receipts    | GET    | `/api/receipts`                         | Paginated receipt history                           |

---

## 5. Resource Responses

### A. ReceiptResource (Example)
```json
{
  "id": 12,
  "ticket_id": "LB-240601-1345-A7D4",
  "status": "placed",
  "receipt_date": "2024-06-01",
  "receipt_date_formatted": "Jun 01, 2024",
  "total_amount": 1000,
  "total_amount_formatted": "1,000",
  "bets": [
    {
      "id": 101,
      "bet_number": "123",
      "amount": "500",
      "amount_formatted": "500",
      "game_type": { "id": 1, "name": "S2" },
      "draw": { "id": 10, "draw_time": "14:00" }
      // ...other fields...
    }
    // ...more bets...
  ],
  "teller": {
    "id": 1, "name": "Jane Teller", "username": "jane", "role": "teller", "location": { "id": 2, "name": "Tacurong Branch" }
  },
  "location": { "id": 2, "name": "Tacurong Branch" },
  "created_at": "2024-06-01T10:00:00Z",
  "created_at_formatted": "Jun 01, 2024 10:00 AM"
}
```

### B. Success Response Example
```json
{
  "status": true,
  "message": "Bet added to receipt",
  "data": { /* ReceiptResource as above */ }
}
```
### C. Error Response Example
```json
{
  "status": false,
  "message": "This number is already in the receipt",
  "data": null
}
```

---

## 6. API Logic and Behavior
- Only one draft receipt per teller (status = 'draft')
- Adding a bet: All validation (sold out, bet cap, win amount, etc.) occurs before adding.
- Removing a bet: Allowed only while in draft.
- Finalizing a receipt: Sets status to `placed`, generates `ticket_id`, assigns `receipt_date`, locks all bets.
- Cancelling a draft: Deletes or marks receipt as `cancelled`, deletes associated bets.
- History: All finalized receipts retrievable for printing, reporting, audit.

---

## 7. Data Formatting
- **Monetary fields** (bet amounts, total_amount): Always return both raw and formatted (string with commas, e.g. "1,000").
- **Dates**: Always return both raw and formatted.
- **Resources**: Always use BetResource, ReceiptResource, UserResource, and LocationResource for consistent, extensible returns.

---

## 8. UI/UX Considerations for Frontend
- App always fetches draft receipt when “Place Bet” screen is opened or on app startup.
- Bet add/remove/update always returns current, updated ReceiptResource for smooth UX.
- Finalize returns the print-ready, placed receipt.
- Old receipts can be fetched/printed from history.
- No draft/cancel/finalize logic on frontend—always via API for security and consistency.

---

## 9. Sample Workflow
1. Teller opens app → `GET /api/receipts/draft`
2. Teller adds a bet → `POST /api/receipts/{id}/bets` (API validates, updates, returns full receipt)
3. Teller removes a bet → `DELETE /api/receipts/{id}/bets/{id}`
4. Teller finalizes receipt → `POST /api/receipts/{id}/place` (ticket_id, print, status placed)
5. App resets for next customer (cycle repeats)

---

## 10. Implementation Notes
- Bet validation logic (cap, sold out, low win, commission, etc.) must match existing business logic—can be refactored into shared service.
- Keep original `placeBet` API for backward compatibility (single bet, no receipt).
- Unit test for receipt workflow and all edge cases (invalid bets, max cap, etc.).
- **Receipt Ticket ID Generation:** Ticket IDs for receipts are generated automatically using an Eloquent model observer when the receipt is finalized (status set to `placed`).

### Example (Laravel Receipt Model Observer)
```php
protected static function booted()
{
    static::creating(function ($receipt) {
        // Only assign ticket_id if receipt is being placed/finalized
        if (empty($receipt->ticket_id) && $receipt->status === 'placed') {
            $prefix = 'LB-';
            $date = now()->format('ymd-Hi'); // yymmdd-hhmm
            $rand = strtoupper(Str::random(4)); // A7D4
            $receipt->ticket_id = $prefix . $date . '-' . $rand;
        }
    });

    // Optional: set ticket_id on update if status changes from draft to placed
    static::updating(function ($receipt) {
        if (empty($receipt->ticket_id) && $receipt->status === 'placed') {
            $prefix = 'LB-';
            $date = now()->format('ymd-Hi');
            $rand = strtoupper(Str::random(4));
            $receipt->ticket_id = $prefix . $date . '-' . $rand;
        }
    });
}
```

---

## 11. Developer Checklist
- [ ] Implement new `receipts` table and `receipt_id` on bets table
- [ ] Add model relationships
- [ ] Build ReceiptResource
- [ ] Add new endpoints in ReceiptController
- [ ] Ensure all responses use ApiResponse helper
- [ ] Document all endpoints with sample responses
- [ ] Test full teller workflow in both backend and frontend

---

**Contact:**
Prepared by: [Your Name]
Date: [Date]
Project: LuckyBet Cart/Receipt Module
Version: 1.0
