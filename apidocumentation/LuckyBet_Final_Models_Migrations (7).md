
# LuckyBet System – Final Models, Migrations, Relationships & Seeders (Corrected)

## Overview
This document contains the complete and corrected structure of all database models, migrations, relationships, and seeders for the LuckyBet system, as per the final prototype. This version removes the unnecessary `schedule_id` from the `draws` table, making `draw_time` standalone as intended.

---


## 👥 Users
### Table: `users`
| Field           | Type     | Notes                                                  |
|------------------|----------|--------------------------------------------------------|
| id               | PK       | Auto increment                                         |
| name             | string   | Full name                                              |
| username         | string   | Login credential                                       |
| email            | string   | Optional                                               |
| phone            | string   | Optional                                               |
| password         | string   | Hashed                                                 |
| role             | enum     | ['admin', 'coordinator', 'teller', 'customer']         |
| location_id      | FK       | Belongs to `locations`                                |
| coordinator_id   | FK/null  | If role = 'teller', FK to `users.id` with role 'coordinator' |
| is_active        | boolean  | Enable/disable access                                  |
| profile_photo_path | string | Optional user photo                                   |
| timestamps        |         | Created/Updated                                       |

#### 🔄 Relationships (within User model)
```php
public function coordinator()
{
    return $this->belongsTo(User::class, 'coordinator_id')->where('role', 'coordinator');
}

public function tellers()
{
    return $this->hasMany(User::class, 'coordinator_id')->where('role', 'teller');
}
```

#### 📌 Notes:
- All roles (`admin`, `coordinator`, `teller`, `customer`) are handled in a **single `users` table**
- Coordinators can manage multiple tellers via the `coordinator_id` FK
- This design supports Laravel relationships, authentication, and flexible RBAC


## 🎮 Game Types
### Table: `game_types`
| Field     | Type    | Notes                  |
|-----------|---------|------------------------|
| id        | PK      | Auto increment         |
| name      | string  | e.g., "2 Digit"        |
| code      | string  | e.g., S2, S3, D4       |
| is_active | boolean | Show/Hide in dropdowns |
| timestamps|         |                        |

### Seeder:
```php
GameType::insert([
  ['name' => '2 Digit', 'code' => 'S2', 'is_active' => true],
  ['name' => '3 Digit', 'code' => 'S3', 'is_active' => true],
  ['name' => '4 Digit', 'code' => 'D4', 'is_active' => true],
]);
```

---

## ⏰ Schedules
### Table: `schedules`
| Field     | Type    | Notes                  |
|-----------|---------|------------------------|
| id        | PK      | Auto increment         |
| name      | string  | e.g., "2:00 PM"        |
| draw_time | time    | Dropdown reference     |
| is_active | boolean |                        |
| timestamps|         |                        |

### Seeder:
```php
Schedule::insert([
  ['name' => '2:00 PM', 'draw_time' => '14:00:00', 'is_active' => true],
  ['name' => '5:00 PM', 'draw_time' => '17:00:00', 'is_active' => true],
  ['name' => '9:00 PM', 'draw_time' => '21:00:00', 'is_active' => true],
]);
```

---

## 🗓️ Draws
### Table: `draws`
| Field       | Type     | Notes                                  |
|-------------|----------|----------------------------------------|
| id          | PK       | Auto increment                         |
| draw_date   | date     | Date of draw                           |
| draw_time   | time     | Time selected from schedule dropdown   |
| is_open     | boolean  | True = Accepting bets                  |
| timestamps  |          |                                        |

---

## 🏆 Results
### Table: `results`
| Field              | Type     | Notes                                 |
|--------------------|----------|---------------------------------------|
| id                 | PK       |                                       |
| draw_id            | FK       | References `draws.id`                 |
| draw_date          | date     | Copy of draw date for reporting       |
| draw_time          | time     | Copy of draw time for reporting       |
| s2_winning_number  | string   |                                       |
| s3_winning_number  | string   |                                       |
| d4_winning_number  | string   |                                       |
| timestamps         |          |                                       |

---

## 💸 Bets
### Table: `bets`

#### 🔎 Why `game_type_id` is a foreign key (FK)

Even though game types (e.g., 2D, 3D, 4D) are selected via dropdown during betting, we retain `game_type_id` as a foreign key in the `bets` table for the following reasons:

- ✅ Ensures only valid game types are used (relational integrity)
- ✅ Enables efficient filtering, grouping, and statistics
- ✅ Allows future extensibility (e.g., game rules, max limits)
- ✅ Prevents duplication and maintains clean, consistent references
- ✅ Supports Laravel Eloquent relationships and eager loading

| Field         | Type     | Notes                                      |
|---------------|----------|--------------------------------------------|
| id            | PK       | Auto increment                             |
| draw_id       | FK       | References `draws.id`                      |
| game_type_id  | FK       | References `game_types.id`                 |
| teller_id     | FK       | References `users.id`                      |
| customer_id   | FK/null  | Optional if assigned                       |
| location_id   | FK       | References `locations.id`                  |
| ticket_id     | string   | Unique ticket ID                           |
| bet_number    | string   | Number being bet on                        |
| amount        | decimal  | Amount wagered                             |
| status        | enum     | ['active','cancelled','won','lost','claimed'] |
| is_combination| boolean  | Combination bet toggle                     |
| bet_date      | datetime | Placed time                                |
| timestamps    |          |                                            |

---

## 💰 Claims
### Table: `claims`
| Field             | Type     | Notes                           |
|-------------------|----------|---------------------------------|
| id                | PK       |                                 |
| bet_id            | FK       | References `bets.id`            |
| result_id         | FK       | References `results.id`         |
| teller_id         | FK       | References `users.id`           |
| amount            | decimal  | Claimed prize                   |
| commission_amount | decimal  | Teller commission cut           |
| status            | enum     | ['pending','processed','rejected'] |
| claim_at          | datetime | When claim was processed        |
| qr_code_data      | text     | Optional encoded ticket info    |
| timestamps        |          |                                 |

---

## 🧾 Commissions
### Table: `commissions`
| Field           | Type     | Notes                            |
|-----------------|----------|----------------------------------|
| id              | PK       |                                  |
| teller_id       | FK       | References `users.id`            |
| type            | enum     | ['bet','claim']                  |
| rate            | decimal  | Percent                          |
| amount          | decimal  | Computed payout                  |
| bet_id          | FK/null  | Optional                         |
| claim_id        | FK/null  | Optional                         |
| commission_date | date     | Date it applies to               |
| timestamps      |          |                                  |

---

## 📊 Tally Sheets
### Table: `tally_sheets`
| Field            | Type     | Notes                              |
|------------------|----------|------------------------------------|
| id               | PK       |                                    |
| teller_id        | FK       | References `users.id`              |
| location_id      | FK       | References `locations.id`          |
| sheet_date       | date     |                                    |
| total_sales      | decimal  | Total bet amount                   |
| total_claims     | decimal  | Total winnings paid                |
| total_commission | decimal  | From both bet and claim types      |
| net_amount       | decimal  | sales - claims - commission        |
| timestamps       |          |                                    |

---

## 📍 Locations
### Table: `locations`
| Field     | Type    | Notes           |
|-----------|---------|-----------------|
| id        | PK      |                 |
| name      | string  | Branch name     |
| address   | string  |                 |
| is_active | boolean |                 |
| timestamps|         |                 |

---


---

## 📊 Bet Ratios
### Table: `bet_ratios`
| Field               | Type     | Notes                                  |
|--------------------|----------|----------------------------------------|
| id                 | PK       |                                        |
| coordinator_id     | FK       | References `users.id` (role: coordinator) |
| draw_date          | date     | Date these limits apply to             |
| s2_limit           | decimal  | Optional                               |
| s3_limit           | decimal  | Optional                               |
| d4_limit           | decimal  | Optional                               |
| s2_win_amount      | decimal  | Optional                               |
| s3_win_amount      | decimal  | Optional                               |
| d4_win_amount      | decimal  | Optional                               |
| s2_low_win_amount  | decimal  | Optional                               |
| s3_low_win_amount  | decimal  | Optional                               |
| d4_low_win_amount  | decimal  | Optional                               |
| timestamps         |          |                                        |

### Table: `bet_ratio_restrictions`
| Field         | Type     | Notes                                   |
|---------------|----------|-----------------------------------------|
| id            | PK       |                                         |
| bet_ratio_id  | FK       | References `bet_ratios.id`              |
| game_type_id  | FK       | References `game_types.id`              |
| number        | string   | Specific bet number                     |
| amount_limit  | decimal  | Limit for this number                   |
| draw_time     | time     | Optional: target specific draw time     |
| timestamps    |          |                                         |

---

## 🚫 Sold Out Numbers
### Table: `sold_out_numbers`
| Field          | Type     | Notes                                   |
|----------------|----------|-----------------------------------------|
| id             | PK       |                                         |
| coordinator_id | FK       | References `users.id` (coordinator)     |
| draw_date      | date     |                                         |
| draw_time      | time     |                                         |
| game_type_id   | FK       | References `game_types.id`              |
| bet_number     | string   | Number that is sold out                 |
| reason         | string   | Optional (e.g., "Temporary Close")      |
| timestamps     |          |                                         |

---

## ⚠️ Low Win Numbers
### Table: `low_win_numbers`
| Field          | Type     | Notes                                   |
|----------------|----------|-----------------------------------------|
| id             | PK       |                                         |
| coordinator_id | FK       | References `users.id`                   |
| draw_date      | date     |                                         |
| draw_time      | time     |                                         |
| game_type_id   | FK       | References `game_types.id`              |
| bet_number     | string   | Number flagged for low win              |
| reason         | string   | Optional (e.g., "Low Win Number")       |
| timestamps     |          |                                         |


## 🔁 Relationships Summary

### User
- belongsTo Location
- hasMany Bets
- hasMany Claims
- hasMany Commissions

### Location
- hasMany Users
- hasMany Bets
- hasMany TallySheets

### Schedule
- used only as dropdown reference (no FK in draws)

### Draw
- hasMany Bets
- hasOne Result

### Bet
- belongsTo Draw, GameType, User (Teller), Location
- hasOne Claim, Commission (if type = bet)

### Result
- belongsTo Draw
- hasMany Claims

### Claim
- belongsTo Bet, Result, User (Teller)
- hasOne Commission (if type = claim)

### Commission
- belongsTo User, Bet (nullable), Claim (nullable)


### User (Self-Referencing)
- belongsTo User (as coordinator) → Only if role is 'teller'
- hasMany Users (as tellers) → Only if role is 'coordinator'


### TallySheet
- belongsTo User (Teller), Location
