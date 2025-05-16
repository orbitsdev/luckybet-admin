# Place Bet API Update

## Overview
This document outlines the updates made to the Place Bet API to support the new D4 sub-selection feature.

## Changes Made
The following changes were implemented on May 16, 2025:

### 1. Database Migration
- Added a new column `d4_sub_selection` to the `bets` table
- The column is an ENUM type with possible values: 'S2' and 'S3'
- The column is nullable to maintain backward compatibility

```php
Schema::table('bets', function (Blueprint $table) {
    $table->enum('d4_sub_selection', ['s2', 's3'])->nullable()->after('is_combination');
});
```

### 2. API Controller Update
The `placeBet` method in `BettingController.php` has been updated to:

- Add validation for the new field:
  ```php
  'd4_sub_selection' => 'nullable|in:S2,S3'
  ```

- Include the new field in the bet creation:
  ```php
  'd4_sub_selection' => $data['d4_sub_selection'] ?? null
  ```

## API Usage
When placing a bet, clients can now include the optional `d4_sub_selection` parameter:

```json
{
  "bet_number": "1234",
  "amount": 100,
  "draw_id": 1,
  "game_type_id": 2,
  "customer_id": null,
  "is_combination": false,
  "d4_sub_selection": "S2"  // Optional: can be "S2", "S3", or omitted
}
```

## Notes
- The field is optional and will default to `null` if not provided
- Only values "S2" and "S3" are accepted
- Existing functionality remains unchanged for clients not using this feature
