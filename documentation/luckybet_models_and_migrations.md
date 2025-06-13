# LuckyBet System: Models and Database Schema Documentation

## Overview

This document provides a comprehensive overview of the LuckyBet system's core models, database schema, and migrations. It details the relationships between models, key attributes, and special features like the D4 sub-selection functionality.

## Core Models

### 1. Bet Model

The `Bet` model represents individual betting entries in the system.

#### Key Attributes:
- `id`: Primary key
- `bet_number`: The number the user is betting on
- `amount`: Decimal value of the bet amount
- `winning_amount`: Calculated winning amount (if applicable)
- `draw_id`: Foreign key to the Draw model
- `game_type_id`: Foreign key to the GameType model
- `teller_id`: Foreign key to the User model (teller who placed the bet)
- `customer_id`: Foreign key to the User model (optional)
- `location_id`: Foreign key to the Location model
- `bet_date`: Date when the bet was placed
- `ticket_id`: Unique identifier for the bet (nullable)
- `is_claimed`: Boolean indicating if the bet has been claimed
- `is_rejected`: Boolean indicating if the bet has been rejected/canceled
- `is_combination`: Boolean indicating if this is a combination bet
- `d4_sub_selection`: Enum ('S2', 'S3') for D4 game type sub-selections
- `commission_rate`: Commission rate for this bet
- `commission_amount`: Calculated commission amount
- `receipt_id`: Foreign key to the Receipt model
- `claimed_at`: Timestamp when the bet was claimed

#### Relationships:
- `draw()`: Belongs to a Draw
- `gameType()`: Belongs to a GameType
- `teller()`: Belongs to a User (teller)
- `customer()`: Belongs to a User (customer)
- `location()`: Belongs to a Location
- `commission()`: Has one Commission
- `receipt()`: Belongs to a Receipt

#### Special Features:
- `getIsWinnerAttribute()`: Determines if a bet is a winner by comparing with results
- `isHit()`: Checks if a bet is a hit (winner) regardless of claim status
- `getWinningAmountAttribute()`: Calculates winning amount considering low win rules
- `getIsLowWinAttribute()`: Determines if a bet is subject to low win rules
- `scopePlaced()`: Scope for bets with receipts in 'placed' status
- `scopeFinalized()`: Scope for bets with receipts in 'placed' or 'cancelled' status

#### D4 Sub-selection Logic:
The `getIsWinnerAttribute()` method includes special logic for D4 sub-selections:
```php
// D4 sub-selection logic: compare to last 2/3 digits of D4 result, not S2/S3 result fields
if (!$isWinner && $this->d4_sub_selection && $result->d4_winning_number) {
    $sub = strtoupper($this->d4_sub_selection);
    if ($sub === 'S2') {
        // Compare last 2 digits of D4 result to bet number (pad bet number to 2 digits)
        $isWinner = substr($result->d4_winning_number, -2) === str_pad($this->bet_number, 2, '0', STR_PAD_LEFT);
    } else if ($sub === 'S3') {
        // Compare last 3 digits of D4 result to bet number (pad bet number to 3 digits)
        $isWinner = substr($result->d4_winning_number, -3) === str_pad($this->bet_number, 3, '0', STR_PAD_LEFT);
    }
}
```

### 2. Receipt Model

The `Receipt` model represents a collection of bets placed together.

#### Key Attributes:
- `id`: Primary key
- `ticket_id`: Unique identifier for the receipt (auto-generated)
- `teller_id`: Foreign key to the User model (teller)
- `location_id`: Foreign key to the Location model
- `receipt_date`: Date when the receipt was created
- `status`: Status of the receipt (draft, placed, cancelled)
- `total_amount`: Total amount of all bets in the receipt
- `placed_at`: Timestamp when the receipt was placed

#### Relationships:
- `bets()`: Has many Bets
- `teller()`: Belongs to a User (teller)
- `location()`: Belongs to a Location

#### Special Features:
- `calculateTotalAmount()`: Calculates the total amount of all non-rejected bets
- Auto-generation of `ticket_id` when a receipt is placed
- Auto-setting of `receipt_date` and `total_amount` when needed

### 3. Draw Model

The `Draw` model represents a specific draw event in the betting system.

#### Key Attributes:
- `id`: Primary key
- `draw_date`: Date of the draw
- `draw_time`: Time of the draw
- `is_open`: Boolean indicating if the draw is open for betting
- `is_active`: Boolean indicating if the draw is active

#### Relationships:
- `bets()`: Has many Bets
- `result()`: Has one Result
- `betRatios()`: Has many BetRatios
- `lowWinNumbers()`: Has many LowWinNumbers

### 4. Result Model

The `Result` model stores the winning numbers for each draw.

#### Key Attributes:
- `id`: Primary key
- `draw_id`: Foreign key to the Draw model
- `draw_date`: Date of the draw
- `draw_time`: Time of the draw
- `s2_winning_number`: Winning number for S2 game type
- `s3_winning_number`: Winning number for S3 game type
- `d4_winning_number`: Winning number for D4 game type
- `coordinator_id`: Foreign key to the User model (coordinator)

#### Relationships:
- `coordinator()`: Belongs to a User (coordinator)
- `draw()`: Belongs to a Draw

### 5. GameType Model

The `GameType` model represents different types of betting games available.

#### Key Attributes:
- `id`: Primary key
- `name`: Name of the game type
- `code`: Code of the game type (e.g., 'S2', 'S3', 'D4')
- `is_active`: Boolean indicating if the game type is active

#### Relationships:
- `bets()`: Has many Bets
- `draws()`: Has many Draws
- `winningAmounts()`: Has many WinningAmounts
- `betRatios()`: Has many BetRatios
- `lowWinNumbers()`: Has many LowWinNumbers

### 6. BetRatio Model

The `BetRatio` model represents limits on bet amounts for specific numbers.

#### Key Attributes:
- `id`: Primary key
- `draw_id`: Foreign key to the Draw model
- `game_type_id`: Foreign key to the GameType model
- `bet_number`: The number being limited
- `sub_selection`: Sub-selection for D4 game type (S2, S3)
- `max_amount`: Maximum amount allowed for this bet number
- `user_id`: Foreign key to the User model (who set the ratio)
- `location_id`: Foreign key to the Location model

#### Relationships:
- `user()`: Belongs to a User
- `draw()`: Belongs to a Draw
- `gameType()`: Belongs to a GameType
- `location()`: Belongs to a Location
- `betRatioAudit()`: Has many BetRatioAudits

### 7. LowWinNumber Model

The `LowWinNumber` model represents numbers with reduced payouts.

#### Key Attributes:
- `id`: Primary key
- `draw_id`: Foreign key to the Draw model (nullable for global rules)
- `game_type_id`: Foreign key to the GameType model
- `location_id`: Foreign key to the Location model
- `bet_number`: The number with reduced payout (nullable for global rules)
- `winning_amount`: The reduced winning amount
- `is_active`: Boolean indicating if the rule is active
- `reason`: Optional text explaining why this number has a reduced payout
- `user_id`: Foreign key to the User model who created the rule
- `start_date`: Optional date when the rule becomes active
- `end_date`: Optional date when the rule expires
- `d4_sub_selection`: Sub-selection for D4 game type (S2, S3)

#### Relationships:
- `draw()`: Belongs to a Draw
- `gameType()`: Belongs to a GameType
- `location()`: Belongs to a Location
- `user()`: Belongs to a User

### 8. WinningAmount Model

The `WinningAmount` model defines the standard payout amounts for different bet amounts and game types.

#### Key Attributes:
- `id`: Primary key
- `game_type_id`: Foreign key to the GameType model
- `location_id`: Foreign key to the Location model
- `amount`: The bet amount
- `winning_amount`: The standard winning amount for this bet

#### Relationships:
- `gameType()`: Belongs to a GameType
- `location()`: Belongs to a Location

### 9. Commission Model

The `Commission` model tracks commission payments to tellers for bets placed.

#### Key Attributes:
- `id`: Primary key
- `teller_id`: Foreign key to the User model (teller)
- `bet_id`: Foreign key to the Bet model
- `rate`: The commission rate applied

#### Relationships:
- `teller()`: Belongs to a User (teller)
- `bet()`: Belongs to a Bet
- `commissionHistory()`: Has many CommissionHistory records

## Database Migrations

### Key Migrations

#### 1. Create Bets Table (2025_05_06_031800_create_bets_table.php)
```php
Schema::create('bets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('draw_id')->constrained()->onDelete('cascade');
    $table->foreignId('game_type_id')->constrained();
    $table->foreignId('teller_id')->constrained('users');
    $table->foreignId('customer_id')->nullable()->constrained('users');
    $table->foreignId('location_id')->nullable()->constrained('locations');
    $table->string('ticket_id')->unique()->nullable();
    $table->string('bet_number');
    $table->decimal('amount', 10, 2);
    $table->boolean('is_claimed')->default(false);
    $table->boolean('is_rejected')->default(false);
    $table->boolean('is_combination')->default(false);
    $table->dateTime('bet_date');
    $table->timestamps();
});
```

#### 2. Add D4 Sub-selection to Bets (2025_05_14_200222_add_d4_sub_selection_to_bets_table.php)
```php
Schema::table('bets', function (Blueprint $table) {
    $table->enum('d4_sub_selection', ['S2', 'S3'])->nullable()->after('is_combination');
});
```

#### 3. Add Claimed At to Bets (2025_05_14_202616_add_claimed_at_to_bets_table.php)
```php
Schema::table('bets', function (Blueprint $table) {
    $table->timestamp('claimed_at')->nullable();
});
```

#### 4. Add Winning Amount to Bets (2025_05_16_213824_add_winning_amount_to_bets_table.php)
```php
Schema::table('bets', function (Blueprint $table) {
    $table->decimal('winning_amount', 10, 2)->nullable();
});
```

#### 5. Add Commission to Bets (2025_05_22_144904_add_commission_to_bets_table.php)
```php
Schema::table('bets', function (Blueprint $table) {
    $table->decimal('commission_rate', 5, 2)->nullable();
    $table->decimal('commission_amount', 10, 2)->nullable();
});
```

#### 6. Create Receipts Table (2025_06_01_221413_create_receipts_table.php)
```php
Schema::create('receipts', function (Blueprint $table) {
    $table->id();
    $table->string('ticket_id')->unique()->nullable();
    $table->foreignId('teller_id')->constrained('users');
    $table->foreignId('location_id')->constrained('locations');
    $table->date('receipt_date');
    $table->enum('status', ['draft', 'placed', 'cancelled'])->default('draft');
    $table->decimal('total_amount', 10, 2)->default(0);
    $table->timestamps();
});
```

#### 7. Add Receipt ID to Bets (2025_06_01_221440_add_receipt_id_to_bets_table.php)
```php
Schema::table('bets', function (Blueprint $table) {
    $table->foreignId('receipt_id')->nullable()->constrained('receipts')->onDelete('cascade');
});
```

#### 8. Add Sub-selection to Bet Ratios (2025_05_30_102824_add_sub_selection_to_bet_ratios_table.php)
```php
Schema::table('bet_ratios', function (Blueprint $table) {
    $table->enum('sub_selection', ['S2', 'S3'])->nullable()->after('bet_number');
});
```

#### 9. Add D4 Sub-selection to LowWinNumbers
```php
Schema::table('low_win_numbers', function (Blueprint $table) {
    $table->enum('d4_sub_selection', ['S2', 'S3'])->nullable()->after('is_active');
});
```

## D4 Sub-selection Feature

The D4 sub-selection feature is a special functionality that allows bets on the last 2 or 3 digits of a 4-digit D4 result.

### Implementation Details:

1. **Database Structure**:
   - `bets` table has an `enum('d4_sub_selection', ['S2', 'S3'])` column
   - `bet_ratios` table has an `enum('sub_selection', ['S2', 'S3'])` column
   - `low_win_numbers` table has an `enum('d4_sub_selection', ['S2', 'S3'])` column

2. **Winner Determination Logic**:
   - For D4-S2: Compare last 2 digits of D4 result with the bet number
   - For D4-S3: Compare last 3 digits of D4 result with the bet number

3. **API Categorization**:
   - D4 bets with sub-selections appear in their specific sub-category (D4-S2 or D4-S3)
   - D4 bets without sub-selections appear only in the standard D4 category
   - This prevents duplication of bets between categories

4. **UI Implementation**:
   - Forms conditionally show sub-selection fields when game type is D4
   - Tables display sub-selection badges with color coding
   - Statistics and reports categorize bets correctly by sub-selection

## Key Relationships Diagram

```
User (Teller) ──┬── Receipt ──── Bet ──┬── GameType
                │                │     │
                │                │     ├── Draw ──── Result
                │                │     │
                └── Location ─────┼─────┘
                                 │     │
                                 │     ├── LowWinNumber
                                 │     │
                                 │     ├── BetRatio
                                 │     │
                                 │     └── WinningAmount
                                 │
                                 └── Commission ──── CommissionHistory
```

## Database Schema Evolution

The LuckyBet system's database schema has evolved over time with several key enhancements:

1. **Initial Structure** (May 2025):
   - Core tables: users, locations, game_types, schedules, draws, bets, results

2. **D4 Sub-selection Addition** (May 14, 2025):
   - Added d4_sub_selection to bets table
   - Enhanced winner determination logic

3. **Winning Amount Tracking** (May 16, 2025):
   - Added winning_amount to bets table
   - Created winning_amounts table for payout configuration

4. **Commission Tracking** (May 22, 2025):
   - Added commission_rate and commission_amount to bets table
   - Created commission_histories table

5. **Receipt System** (June 1, 2025):
   - Created receipts table
   - Added receipt_id to bets table
   - Implemented receipt status workflow (draft, placed, cancelled)

6. **Audit Trails** (May 21-22, 2025):
   - Created bet_ratio_audits table
   - Created commission_histories table

This documentation provides a comprehensive overview of the LuckyBet system's core models and database schema, highlighting the relationships between entities and the special D4 sub-selection feature.
