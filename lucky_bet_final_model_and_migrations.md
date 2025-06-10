# LuckyBet Final Models and Migrations Documentation

## Database Schema Overview

This document outlines the complete database structure and relationships for the LuckyBet lottery management system.

## Core Models

### User
```php
// Model: app/Models/User.php
// Migration: database/migrations/0001_01_01_000000_create_users_table.php

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('role'); // admin, coordinator, teller, customer
    $table->foreignId('coordinator_id')->nullable()->constrained('users');
    $table->foreignId('location_id')->nullable()->constrained('locations');
    $table->timestamp('email_verified_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
});

// Relationships
- belongsTo(Location::class)
- belongsTo(User::class, 'coordinator_id') // For tellers
- hasMany(User::class, 'coordinator_id') // For coordinators
- hasMany(Bet::class)
- hasMany(Commission::class)
```

### Location
```php
// Model: app/Models/Location.php
// Migration: database/migrations/2025_05_06_031400_create_locations_table.php

Schema::create('locations', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('address')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});

// Relationships
- hasMany(User::class)
- hasMany(Bet::class)
```

### GameType
```php
// Model: app/Models/GameType.php
// Migration: database/migrations/2025_05_06_031500_create_game_types_table.php

Schema::create('game_types', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // S2, S3, D4
    $table->integer('digit_count');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Relationships
- hasMany(Draw::class)
- hasMany(Bet::class)
- hasMany(BetRatio::class)
```

### Schedule
```php
// Model: app/Models/Schedule.php
// Migration: database/migrations/2025_05_06_031600_create_schedules_table.php

Schema::create('schedules', function (Blueprint $table) {
    $table->id();
    $table->time('draw_time');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Relationships
- hasMany(Draw::class)
```

## Draw Management

### Draw
```php
// Model: app/Models/Draw.php
// Migration: database/migrations/2025_05_06_031700_create_draws_table.php

Schema::create('draws', function (Blueprint $table) {
    $table->id();
    $table->date('draw_date');
    $table->time('draw_time');
    $table->foreignId('game_type_id')->constrained('game_types');
    $table->boolean('is_open')->default(true);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});

// Relationships
- belongsTo(GameType::class)
- hasMany(Bet::class)
- hasOne(Result::class)
- hasMany(BetRatio::class)
- hasMany(LowWinNumber::class)
```

### Result
```php
// Model: app/Models/Result.php
// Migration: database/migrations/2025_05_06_031900_create_results_table.php

Schema::create('results', function (Blueprint $table) {
    $table->id();
    $table->foreignId('draw_id')->constrained('draws')->onDelete('cascade');
    $table->string('winning_number');
    $table->date('draw_date');
    $table->time('draw_time');
    $table->timestamps();
});

// Relationships
- belongsTo(Draw::class)
```

## Betting System

### Bet
```php
// Model: app/Models/Bet.php
// Migration: database/migrations/2025_05_06_031800_create_bets_table.php

Schema::create('bets', function (Blueprint $table) {
    $table->id();
    $table->string('ticket_id')->unique();
    $table->foreignId('user_id')->constrained('users');
    $table->foreignId('draw_id')->constrained('draws');
    $table->foreignId('game_type_id')->constrained('game_types');
    $table->foreignId('location_id')->constrained('locations');
    $table->foreignId('receipt_id')->nullable()->constrained('receipts');
    $table->string('bet_number');
    $table->decimal('amount', 10, 2);
    $table->decimal('winning_amount', 10, 2)->nullable();
    $table->decimal('commission', 10, 2)->nullable();
    $table->string('status')->default('pending'); // pending, won, lost, claimed, cancelled
    $table->string('d4_sub_selection')->nullable(); // For D4-S2, D4-S3
    $table->timestamp('claimed_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

// Relationships
- belongsTo(User::class)
- belongsTo(Draw::class)
- belongsTo(GameType::class)
- belongsTo(Location::class)
- belongsTo(Receipt::class)
```

### BetRatio
```php
// Model: app/Models/BetRatio.php
// Migration: database/migrations/2025_05_06_032500_create_bet_ratios_table.php

Schema::create('bet_ratios', function (Blueprint $table) {
    $table->id();
    $table->foreignId('draw_id')->constrained('draws');
    $table->foreignId('game_type_id')->constrained('game_types');
    $table->string('number');
    $table->decimal('max_amount', 10, 2); // 0 means sold out
    $table->string('sub_selection')->nullable(); // For D4-S2, D4-S3
    $table->timestamps();
});

// Relationships
- belongsTo(Draw::class)
- belongsTo(GameType::class)
- hasMany(BetRatioAudit::class)
```

### LowWinNumber
```php
// Model: app/Models/LowWinNumber.php
// Migration: database/migrations/2025_05_06_032800_create_low_win_numbers_table.php

Schema::create('low_win_numbers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('draw_id')->constrained('draws');
    $table->foreignId('game_type_id')->constrained('game_types');
    $table->string('number');
    $table->decimal('winning_amount', 10, 2);
    $table->timestamps();
});

// Relationships
- belongsTo(Draw::class)
- belongsTo(GameType::class)
```

## Financial Management

### Commission
```php
// Model: app/Models/Commission.php
// Migration: database/migrations/2025_05_06_032100_create_commissions_table.php

Schema::create('commissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->decimal('amount', 10, 2);
    $table->string('status')->default('pending'); // pending, paid
    $table->date('commission_date');
    $table->timestamps();
});

// Relationships
- belongsTo(User::class)
- hasMany(CommissionHistory::class)
```

### Receipt
```php
// Model: app/Models/Receipt.php
// Migration: database/migrations/2025_06_01_221413_create_receipts_table.php

Schema::create('receipts', function (Blueprint $table) {
    $table->id();
    $table->string('receipt_number')->unique();
    $table->foreignId('user_id')->constrained('users');
    $table->decimal('total_amount', 10, 2);
    $table->timestamps();
});

// Relationships
- belongsTo(User::class)
- hasMany(Bet::class)
```

### WinningAmount
```php
// Model: app/Models/WinningAmount.php
// Migration: database/migrations/2025_05_16_205104_create_winning_amounts_table.php

Schema::create('winning_amounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('game_type_id')->constrained('game_types');
    $table->decimal('amount', 10, 2);
    $table->timestamps();
});

// Relationships
- belongsTo(GameType::class)
```

## Audit Tables

### BetRatioAudit
```php
// Model: app/Models/BetRatioAudit.php
// Migration: database/migrations/2025_05_21_141936_create_bet_ratio_audits_table.php

Schema::create('bet_ratio_audits', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bet_ratio_id')->constrained('bet_ratios');
    $table->decimal('old_max_amount', 10, 2);
    $table->decimal('new_max_amount', 10, 2);
    $table->foreignId('user_id')->constrained('users');
    $table->timestamps();
});

// Relationships
- belongsTo(BetRatio::class)
- belongsTo(User::class)
```

### CommissionHistory
```php
// Model: app/Models/CommissionHistory.php
// Migration: database/migrations/2025_05_22_144730_create_commission_histories_table.php

Schema::create('commission_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('commission_id')->constrained('commissions');
    $table->string('action'); // created, updated, paid
    $table->foreignId('user_id')->constrained('users');
    $table->timestamps();
});

// Relationships
- belongsTo(Commission::class)
- belongsTo(User::class)
```

## Key Features and Notes

1. **Role-based User System**
   - Single User model with role differentiation (admin, coordinator, teller, customer)
   - Hierarchical structure with coordinators managing tellers

2. **Multi-game Support**
   - Supports S2, S3, D4 game types
   - D4 has subtypes (D4-S2, D4-S3) for additional betting options

3. **Draw Management**
   - Multiple game types per draw time
   - Flexible scheduling system
   - Support for marking draws as open/closed

4. **Betting Controls**
   - Bet ratio system for limiting bet amounts
   - Sold out numbers (implemented via bet ratios with max_amount = 0)
   - Low win numbers for reduced payouts
   - Complete audit trail for ratio changes

5. **Financial Tracking**
   - Commission system for tellers
   - Receipt generation for bets
   - Winning amount configuration per game type
   - Commission history tracking

6. **Data Integrity**
   - Soft deletes on critical tables
   - Cascade deletes where appropriate
   - Audit trails for sensitive operations

7. **Security Features**
   - Email verification
   - Two-factor authentication support
   - Remember token functionality
   - Personal access tokens for API access
