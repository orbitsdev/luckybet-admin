# Models, Migrations, and Relationships Documentation

_Last updated: 2025-05-22 16:01:07 (UTC+8)_

---

## Table of Contents
- [User](#user)
- [Bet](#bet)
- [BetRatio](#betratio)
- [BetRatioAudit](#betratioaudit)
- [Commission](#commission)
- [CommissionHistory](#commissionhistory)
- [Draw](#draw)
- [GameType](#gametype)
- [Location](#location)
- [LowWinNumber](#lowwinnumber)
- [Result](#result)
- [Schedule](#schedule)
- [WinningAmount](#winningamount)

---

## User
**Table:** users

| Column              | Type     | Notes                         |
|---------------------|----------|-------------------------------|
| id                  | bigint   | Primary key                   |
| name                | string   |                               |
| username            | string   | Unique                        |
| email               | string   | Unique                        |
| phone               | string   | Nullable                      |
| password            | string   |                               |
| email_verified_at   | timestamp| Nullable                      |
| remember_token      | string   | Nullable                      |
| current_team_id     | bigint   | Nullable                      |
| profile_photo_path  | string   | Nullable                      |
| role                | enum     | admin, coordinator, teller, customer |
| coordinator_id      | bigint   | Nullable, FK to users         |
| is_active           | boolean  | Default true                  |
| location_id         | bigint   | Nullable, FK to locations     |
| created_at/updated_at| timestamp|                               |

**Model $fillable:**
```
['username','password','name','email','phone','role','profile_photo_path','email_verified_at','current_team_id','remember_token','is_active','location_id','coordinator_id']
```

**Relationships:**
- hasMany: Bet (as teller)
- hasOne: Commission (as teller)
- hasMany: LowWinNumber (set by user)
- belongsTo: Location
- belongsTo: User (coordinator)

---

## Bet
**Table:** bets

| Column              | Type     | Notes                         |
|---------------------|----------|-------------------------------|
| id                  | bigint   | Primary key                   |
| draw_id             | bigint   | FK to draws                   |
| game_type_id        | bigint   | FK to game_types              |
| teller_id           | bigint   | FK to users                   |
| customer_id         | bigint   | Nullable, FK to users         |
| location_id         | bigint   | Nullable, FK to locations     |
| ticket_id           | string   | Unique, Nullable              |
| bet_number          | string   |                               |
| amount              | decimal  | (10,2)                        |
| winning_amount      | decimal  | (10,2), Nullable              |
| commission_rate     | decimal  | (5,4), Nullable               |
| commission_amount   | decimal  | (10,2), Nullable              |
| is_claimed          | boolean  | Default false                 |
| is_rejected         | boolean  | Default false                 |
| is_combination      | boolean  | Default false                 |
| d4_sub_selection    | enum     | S2, S3, Nullable              |
| bet_date            | datetime |                               |
| claimed_at          | timestamp| Nullable                      |
| created_at/updated_at| timestamp|                               |

**Model $fillable:**
```
['bet_number','amount','winning_amount','draw_id','game_type_id','teller_id','customer_id','location_id','bet_date','ticket_id','is_claimed','is_rejected','is_combination','d4_sub_selection','commission_rate','commission_amount']
```

**Relationships:**
- belongsTo: Draw, GameType, User (teller), User (customer), Location
- hasOne: Commission

---

## BetRatio
**Table:** bet_ratios

| Column       | Type    | Notes                  |
|--------------|---------|------------------------|
| id           | bigint  | Primary key            |
| draw_id      | bigint  | FK to draws            |
| game_type_id | bigint  | FK to game_types       |
| bet_number   | string  |                        |
| max_amount   | decimal | (10,2)                 |
| user_id      | bigint  | Nullable, FK to users  |
| location_id  | bigint  | Nullable, FK to locations |
| created_at/updated_at| timestamp|             |

**Model $fillable:**
```
['draw_id','game_type_id','bet_number','max_amount','user_id','location_id']
```

**Relationships:**
- belongsTo: Draw, GameType, User, Location
- hasMany: BetRatioAudit

---

## BetRatioAudit
**Table:** bet_ratio_audits

| Column         | Type    | Notes                |
|----------------|---------|----------------------|
| id             | bigint  | Primary key          |
| bet_ratio_id   | bigint  | FK to bet_ratios     |
| user_id        | bigint  | FK to users          |
| old_max_amount | decimal | (10,2), Nullable     |
| new_max_amount | decimal | (10,2)               |
| action         | string  | e.g., set, update    |
| created_at/updated_at| timestamp|             |

**Model $fillable:**
```
['bet_ratio_id','user_id','old_max_amount','new_max_amount','action']
```

**Relationships:**
- belongsTo: BetRatio, User

---

## Commission
**Table:** commissions

| Column     | Type    | Notes             |
|------------|---------|-------------------|
| id         | bigint  | Primary key       |
| teller_id  | bigint  | FK to users       |
| rate       | decimal | (5,4)             |
| bet_id     | bigint  | FK to bets        |
| created_at/updated_at| timestamp|        |

**Model $fillable:**
```
['teller_id','rate','bet_id']
```

**Relationships:**
- belongsTo: User (teller), Bet
- hasMany: CommissionHistory

---

## CommissionHistory
**Table:** commission_histories

| Column       | Type    | Notes                  |
|--------------|---------|------------------------|
| id           | bigint  | Primary key            |
| commission_id| bigint  | FK to commissions      |
| old_rate     | decimal | (5,4)                  |
| new_rate     | decimal | (5,4)                  |
| changed_by   | bigint  | Nullable, FK to users  |
| changed_at   | timestamp| Default now           |
| created_at/updated_at| timestamp|             |

**Model $fillable:**
```
['commission_id','old_rate','new_rate','changed_by','changed_at']
```

**Relationships:**
- belongsTo: Commission, User (changed_by)

---

## Draw
**Table:** draws

| Column     | Type    | Notes             |
|------------|---------|-------------------|
| id         | bigint  | Primary key       |
| draw_date  | date    |                   |
| draw_time  | time    |                   |
| is_open    | boolean | Default true      |
| created_at/updated_at| timestamp|        |

**Model $fillable:**
```
['draw_date','draw_time','is_open']
```

**Relationships:**
- hasMany: Bet, BetRatio, LowWinNumber
- hasOne: Result

---

## GameType
**Table:** game_types

| Column     | Type    | Notes             |
|------------|---------|-------------------|
| id         | bigint  | Primary key       |
| name       | string  |                   |
| code       | string  | Unique, max 5     |
| digit_count| integer | Default 2         |
| is_active  | boolean | Default true      |
| created_at/updated_at| timestamp|        |

**Model $fillable:**
```
['name','code','is_active']
```

**Relationships:**
- hasMany: Bet, Draw, WinningAmount, BetRatio, LowWinNumber

---

## Location
**Table:** locations

| Column     | Type    | Notes             |
|------------|---------|-------------------|
| id         | bigint  | Primary key       |
| name       | string  |                   |
| address    | string  |                   |
| is_active  | boolean | Default true      |
| created_at/updated_at| timestamp|        |

**Model $fillable:**
```
['name','address','is_active']
```

**Relationships:**
- hasMany: User, Bet, BetRatio, LowWinNumber, WinningAmount

---

## LowWinNumber
**Table:** low_win_numbers

| Column       | Type    | Notes                  |
|--------------|---------|------------------------|
| id           | bigint  | Primary key            |
| draw_id      | bigint  | Nullable, FK to draws  |
| game_type_id | bigint  | FK to game_types       |
| bet_number   | string  |                        |
| winning_amount| decimal| (18,2)                 |
| user_id      | bigint  | Nullable, FK to users  |
| locations_id | bigint  | Nullable, FK to locations |
| reason       | string  | Nullable               |
| created_at/updated_at| timestamp|             |

**Model $fillable:**
```
['draw_id','game_type_id','bet_number','winning_amount','reason','user_id','locations_id']
```

**Relationships:**
- belongsTo: Draw, GameType, User, Location

---

## Result
**Table:** results

| Column            | Type    | Notes             |
|-------------------|---------|-------------------|
| id                | bigint  | Primary key       |
| draw_id           | bigint  | FK to draws       |
| draw_date         | date    | Nullable          |
| draw_time         | time    | Nullable          |
| s2_winning_number | string  | Nullable          |
| s3_winning_number | string  | Nullable          |
| d4_winning_number | string  | Nullable          |
| created_at/updated_at| timestamp|                |

**Model $fillable:**
```
['draw_id','draw_date','draw_time','s2_winning_number','s3_winning_number','d4_winning_number']
```

**Relationships:**
- belongsTo: Draw
- hasMany: Claim

---

## Schedule
**Table:** schedules

| Column     | Type    | Notes             |
|------------|---------|-------------------|
| id         | bigint  | Primary key       |
| name       | string  |                   |
| draw_time  | time    |                   |
| is_active  | boolean | Default true      |
| created_at/updated_at| timestamp|        |

**Model $fillable:**
```
['name','draw_time','is_active']
```

**Relationships:**
- hasMany: Draw

---

## WinningAmount
**Table:** winning_amounts

| Column        | Type    | Notes                  |
|---------------|---------|------------------------|
| id            | bigint  | Primary key            |
| location_id   | bigint  | Nullable, FK to locations |
| game_type_id  | bigint  | FK to game_types       |
| amount        | decimal | (18,2)                 |
| winning_amount| decimal | (18,2)                 |
| created_at/updated_at| timestamp|              |

**Model $fillable:**
```
['game_type_id','amount','winning_amount']
```

**Relationships:**
- belongsTo: GameType, Location
