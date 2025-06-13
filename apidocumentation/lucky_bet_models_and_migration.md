# LuckyBet Admin - Models and Database Structure

This document provides a comprehensive overview of the LuckyBet Admin system's database structure, including models, relationships, and migrations. It serves as a reference for understanding how the different components of the system interact with each other.

## Table of Contents
- [Database Schema Overview](#database-schema-overview)
- [Core Models](#core-models)
  - [User](#user)
  - [Draw](#draw)
  - [Bet](#bet)
  - [Result](#result)
  - [GameType](#gametype)
  - [Location](#location)
  - [Schedule](#schedule)
  - [BetRatio](#betratio)
  - [BetRatioAudit](#betratio-audit)
  - [LowWinNumber](#lowwinnumber)
  - [Commission](#commission)
  - [CommissionHistory](#commission-history)
  - [WinningAmount](#winningamount)
- [Relationships Visualization](#relationships-visualization)
- [Key Business Scenarios](#key-business-scenarios)
- [Migrations](#migrations)

## Database Schema Overviewrec

The LuckyBet Admin system is built around several key entities:

1. **Users** - Administrators, coordinators, tellers, and customers
2. **Draws** - Scheduled lottery draws with dates and times
3. **Bets** - Wagers placed by customers through tellers
4. **Results** - Winning numbers for each draw
5. **Game Types** - Different types of lottery games (S2, S3, D4)
6. **Locations** - Physical locations where bets are placed
7. **Schedules** - Predefined draw times
8. **Bet Ratios** - Maximum bet amounts for specific numbers
9. **Bet Ratio Audits** - Track changes to bet ratios
10. **Low Win Numbers** - Numbers with reduced payouts
11. **Commissions** - Teller commissions on bets
12. **Commission Histories** - Track changes to commission rates
13. **Winning Amounts** - Configuration for payout calculations

## Core Models

### User

The User model represents all users in the system with different roles.

```php
class User extends Authenticatable implements HasMedia
{
    // Relationships
    public function location() {
        return $this->belongsTo(Location::class);
    }
    
    public function bets() {
        return $this->hasMany(Bet::class, 'teller_id');
    }
    
    public function commission() {
        return $this->hasOne(Commission::class, 'teller_id');
    }
    
    public function lowWinNumbers() {
        return $this->hasMany(LowWinNumber::class);
    }
    
    public function coordinator() {
        return $this->belongsTo(User::class, 'coordinator_id')->where('role', 'coordinator');
    }
    
    public function tellers() {
        return $this->hasMany(User::class, 'coordinator_id')->where('role', 'teller');
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `name` - User's full name
- `username` - Unique username for login
- `password` - Hashed password
- `email` - Email address
- `phone` - Contact number
- `role` - User role (admin, coordinator, teller, customer)
- `location_id` - Associated location
- `coordinator_id` - For tellers, their supervising coordinator
- `is_active` - Whether the user account is active
- `profile_photo_path` - Path to the user's profile photo

**Relationships:**
- Belongs to one Location
- Has many Bets (as teller)
- Has one Commission (as teller)
- Has many LowWinNumbers (as creator)
- Belongs to one Coordinator (if teller)
- Has many Tellers (if coordinator)
- Has many BetRatios (as creator)

### Draw

The Draw model represents scheduled lottery draws.

```php
class Draw extends Model
{
    // Relationships
    public function bets(): HasMany {
        return $this->hasMany(Bet::class);
    }
    
    public function result(): HasOne {
        return $this->hasOne(Result::class);
    }
    
    public function betRatios() {
        return $this->hasMany(BetRatio::class);
    }
    
    public function lowWinNumbers() {
        return $this->hasMany(LowWinNumber::class);
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `draw_date` - Date of the draw
- `draw_time` - Time of the draw
- `is_open` - Whether bets can still be placed
- `is_active` - Whether the draw is active

**Relationships:**
- Has many Bets
- Has one Result
- Has many BetRatios
- Has many LowWinNumbers

### Bet

The Bet model represents wagers placed by customers.

```php
class Bet extends Model
{
    // Relationships
    public function draw() {
        return $this->belongsTo(Draw::class);
    }
    
    public function gameType() {
        return $this->belongsTo(GameType::class);
    }
    
    public function teller() {
        return $this->belongsTo(User::class, 'teller_id');
    }
    
    public function customer() {
        return $this->belongsTo(User::class, 'customer_id');
    }
    
    public function location() {
        return $this->belongsTo(Location::class);
    }
    
    public function commission() {
        return $this->hasOne(Commission::class, 'bet_id');
    }
    
    // Winner detection logic
    public function getIsWinnerAttribute($ignoreClaimStatus = false) {
        // Logic to determine if bet is a winner
        // For D4 sub-selections, compares last 2 or 3 digits of D4 winning number
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `bet_number` - The number bet on
- `amount` - Bet amount
- `winning_amount` - Payout amount if won
- `draw_id` - Associated draw
- `game_type_id` - Type of game (S2, S3, D4)
- `teller_id` - Teller who processed the bet
- `customer_id` - Customer who placed the bet
- `location_id` - Location where bet was placed
- `bet_date` - Date the bet was placed
- `ticket_id` - Unique ticket identifier
- `is_claimed` - Whether winnings have been claimed
- `is_rejected` - Whether the bet was rejected
- `is_combination` - Whether this is a combination bet
- `d4_sub_selection` - For D4 bets, can be S2 or S3 sub-selection
- `commission_rate` - Commission rate for the teller
- `commission_amount` - Commission amount for the teller

**Relationships:**
- Belongs to one Draw
- Belongs to one GameType
- Belongs to one User as teller
- Belongs to one User as customer (optional)
- Belongs to one Location
- Has one Commission

### Result

The Result model stores winning numbers for each draw.

```php
class Result extends Model
{
    // Relationships
    public function draw() {
        return $this->belongsTo(Draw::class);
    }
    
    public function coordinator() {
        return $this->belongsTo(User::class, 'coordinator_id');
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `draw_id` - Associated draw
- `draw_date` - Date of the draw
- `draw_time` - Time of the draw
- `s2_winning_number` - Winning number for S2 game
- `s3_winning_number` - Winning number for S3 game
- `d4_winning_number` - Winning number for D4 game
- `coordinator_id` - User who entered the result

**Relationships:**
- Belongs to one Draw
- Belongs to one User as coordinator

### GameType

The GameType model represents different types of lottery games.

```php
class GameType extends Model
{
    // Relationships
    public function bets() {
        return $this->hasMany(Bet::class);
    }
    
    public function draws() {
        return $this->hasMany(Draw::class);
    }
    
    public function betRatios() {
        return $this->hasMany(BetRatio::class);
    }
    
    public function lowWinNumbers() {
        return $this->hasMany(LowWinNumber::class);
    }
    
    public function winningAmounts() {
        return $this->hasMany(WinningAmount::class);
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `name` - Game type name (S2, S3, D4)
- `code` - Game type code
- `is_active` - Whether the game type is active

**Relationships:**
- Has many Bets
- Has many Draws
- Has many BetRatios
- Has many LowWinNumbers
- Has many WinningAmounts

### Location

The Location model represents physical locations where bets are placed.

```php
class Location extends Model
{
    // Relationships
    public function users() {
        return $this->hasMany(User::class);
    }
    
    public function bets() {
        return $this->hasMany(Bet::class);
    }
    
    public function betRatios() {
        return $this->hasMany(BetRatio::class);
    }
    
    public function lowWinNumbers() {
        return $this->hasMany(LowWinNumber::class);
    }
    
    public function winningAmounts() {
        return $this->hasMany(WinningAmount::class);
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `name` - Location name
- `address` - Physical address
- `is_active` - Whether the location is active

**Relationships:**
- Has many Users
- Has many Bets
- Has many BetRatios
- Has many LowWinNumbers
- Has many WinningAmounts

### Schedule

The Schedule model represents predefined draw times.

```php
class Schedule extends Model
{
    // No explicit relationships defined in the model
}
```

**Key Attributes:**
- `id` - Primary key
- `name` - Schedule name (e.g., "2:00 PM")
- `draw_time` - System time format
- `is_active` - Whether the schedule is active/visible

**Relationships:**
- No explicit relationships defined in the model

### BetRatio

The BetRatio model defines maximum bet amounts for specific numbers.

```php
class BetRatio extends Model
{
    // Relationships
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function draw() {
        return $this->belongsTo(Draw::class);
    }
    
    public function gameType() {
        return $this->belongsTo(GameType::class);
    }
    
    public function location() {
        return $this->belongsTo(Location::class);
    }
    
    public function betRatioAudit() {
        return $this->hasMany(BetRatioAudit::class);
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `draw_id` - Associated draw
- `game_type_id` - Associated game type
- `bet_number` - The specific number
- `sub_selection` - For D4 bets, can be S2 or S3 sub-selection
- `max_amount` - Maximum bet amount allowed
- `user_id` - User who set this ratio
- `location_id` - Associated location

**Relationships:**
- Belongs to one User
- Belongs to one Draw
- Belongs to one GameType
- Belongs to one Location
- Has many BetRatioAudits

### BetRatioAudit

The BetRatioAudit model tracks changes to bet ratios.

```php
class BetRatioAudit extends Model
{
    // Relationships
    public function betRatio() {
        return $this->belongsTo(BetRatio::class);
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `bet_ratio_id` - Associated bet ratio
- `user_id` - User who made the change
- `old_max_amount` - Previous maximum amount
- `new_max_amount` - New maximum amount
- `action` - Type of action performed

**Relationships:**
- Belongs to one BetRatio

### LowWinNumber

The LowWinNumber model defines numbers with reduced payouts.

```php
class LowWinNumber extends Model
{
    // Relationships
    public function gameType() {
        return $this->belongsTo(GameType::class);
    }
    
    public function draw() {
        return $this->belongsTo(Draw::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function location() {
        return $this->belongsTo(Location::class);
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `draw_id` - Associated draw
- `game_type_id` - Associated game type
- `bet_number` - The low win number
- `winning_amount` - Reduced payout amount
- `reason` - Reason for reduced payout
- `user_id` - User who set this number
- `location_id` - Associated location

**Relationships:**
- Belongs to one Draw
- Belongs to one GameType
- Belongs to one User (creator)
- Belongs to one Location

### Commission

The Commission model tracks teller commissions.

```php
class Commission extends Model
{
    // Relationships
    public function teller() {
        return $this->belongsTo(User::class, 'teller_id');
    }
    
    public function bet() {
        return $this->belongsTo(Bet::class);
    }
    
    public function commissionHistory() {
        return $this->hasMany(CommissionHistory::class);
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `teller_id` - Associated teller
- `bet_id` - Associated bet
- `rate` - Commission rate

**Relationships:**
- Belongs to one User as teller
- Belongs to one Bet
- Has many CommissionHistories

### CommissionHistory

The CommissionHistory model tracks changes to commission rates.

```php
class CommissionHistory extends Model
{
    // Relationships
    public function commission() {
        return $this->belongsTo(Commission::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `commission_id` - Associated commission
- `old_rate` - Previous commission rate
- `new_rate` - New commission rate
- `changed_by` - User who made the change
- `changed_at` - When the change was made

**Relationships:**
- Belongs to one Commission
- Belongs to one User who made the change

### WinningAmount

The WinningAmount model configures payout calculations.

```php
class WinningAmount extends Model
{
    // Relationships
    public function gameType() {
        return $this->belongsTo(GameType::class);
    }
    
    public function location() {
        return $this->belongsTo(Location::class);
    }
}
```

**Key Attributes:**
- `id` - Primary key
- `game_type_id` - Associated game type
- `amount` - Bet amount
- `winning_amount` - Payout amount

**Relationships:**
- Belongs to one GameType
- Belongs to one Location

## Relationships Visualization

```
User
├── Location (belongs to)
├── Bets (has many as teller)
├── Commission (has one as teller)
├── LowWinNumbers (has many as creator)
├── BetRatios (has many as creator)
├── Coordinator (belongs to if teller)
└── Tellers (has many if coordinator)

Draw
├── Bets (has many)
├── Result (has one)
├── BetRatios (has many)
└── LowWinNumbers (has many)

Bet
├── Draw (belongs to)
├── GameType (belongs to)
├── Teller (belongs to User)
├── Customer (belongs to User, optional)
├── Location (belongs to)
└── Commission (has one)

Result
├── Draw (belongs to)
└── Coordinator (belongs to User)

GameType
├── Bets (has many)
├── Draws (has many)
├── BetRatios (has many)
├── LowWinNumbers (has many)
└── WinningAmounts (has many)

Location
├── Users (has many)
├── Bets (has many)
├── BetRatios (has many)
├── LowWinNumbers (has many)
└── WinningAmounts (has many)

Schedule
└── (No explicit relationships defined)

BetRatio
├── User (belongs to)
├── Draw (belongs to)
├── GameType (belongs to)
├── Location (belongs to)
└── BetRatioAudits (has many)

BetRatioAudit
└── BetRatio (belongs to)

LowWinNumber
├── Draw (belongs to)
├── GameType (belongs to)
├── User (belongs to)
└── Location (belongs to)

Commission
├── Teller (belongs to User)
├── Bet (belongs to)
└── CommissionHistories (has many)

CommissionHistory
├── Commission (belongs to)
└── User (belongs to, as changed_by)

WinningAmount
├── GameType (belongs to)
└── Location (belongs to)
```

## Key Business Scenarios

### 1. Placing a Bet

When a customer places a bet:
1. A teller creates a new Bet record
2. The bet is associated with a specific Draw, GameType, and Location
3. The system checks BetRatio to ensure the bet amount doesn't exceed limits
4. If the bet is placed, a Commission record is created for the teller

### 2. Drawing Results

When a draw is completed:
1. An admin creates a Result record with winning numbers
2. The system automatically identifies winning bets by:
   - For S2: Matching bet_number with s2_winning_number
   - For S3: Matching bet_number with s3_winning_number
   - For D4: Matching bet_number with d4_winning_number
   - For D4-S2: Matching bet_number with last 2 digits of d4_winning_number
   - For D4-S3: Matching bet_number with last 3 digits of d4_winning_number
3. Winning amounts are calculated based on WinningAmount multipliers
4. Low win numbers have reduced payouts

### 3. Claiming Winnings

When a winner claims their prize:
1. A teller marks the bet as claimed (is_claimed = true)
2. The claimed_at timestamp is recorded
3. The winning amount is paid to the customer

## Migrations

The database structure is defined through the following key migrations:

1. **Users Table**
   - Creates the basic user structure
   - Adds two-factor authentication columns
   - Adds location foreign key
   - Adds coordinator relationship

2. **Locations Table**
   - Creates locations with name, address, and active status

3. **Game Types Table**
   - Creates game types (S2, S3, D4)

4. **Draws Table**
   - Creates draws with date, time, and open status
   - Later adds is_active flag

5. **Bets Table**
   - Creates the core bet structure
   - Later adds d4_sub_selection field
   - Later adds claimed_at timestamp
   - Later adds winning_amount field
   - Later adds commission fields

6. **Results Table**
   - Creates results with winning numbers for each game type
   - Updates foreign key to cascade on delete

7. **Commissions Table**
   - Creates commissions with rate and amount
   - Later adds commission histories

8. **Bet Ratios Table**
   - Creates bet ratios with max amounts
   - Later adds d4_sub_selection field
   - Later adds bet ratio audits

9. **Low Win Numbers Table**
   - Creates low win numbers

10. **Winning Amounts Table**
    - Creates winning amount configurations with multipliers

This database structure supports all the key functionality of the LuckyBet Admin system, including bet management, draw results, winner detection, and financial tracking.
