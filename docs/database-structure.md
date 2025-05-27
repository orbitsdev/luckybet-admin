# LuckyBet Admin Database Structure

This document outlines the database structure, relationships, and migrations for the LuckyBet Admin system. It is intended for frontend developers to understand the data model when integrating with the backend API.

## Table of Contents

- [Overview](#overview)
- [Database Tables](#database-tables)
  - [Users](#users)
  - [Game Types](#game-types)
  - [Draws](#draws)
  - [Results](#results)
  - [Bets](#bets)
  - [Bet Ratios](#bet-ratios)
  - [Low Win Numbers](#low-win-numbers)
  - [Winning Amounts](#winning-amounts)
  - [Commissions](#commissions)
  - [Schedules](#schedules)
  - [Locations](#locations)
- [Key Relationships](#key-relationships)
- [Entity Relationship Diagram](#entity-relationship-diagram)
- [Migration Files](#migration-files)

## Overview

The LuckyBet Admin system is a lottery management application that supports multiple game types (S2, S3, D4) for each draw time. The system allows for:

- Managing draws and their schedules
- Processing bets from tellers
- Recording and displaying results
- Managing winning amounts and commissions
- Setting bet limits through bet ratios
- Handling special cases with low win numbers

## Database Tables

### Users

The `users` table stores all user accounts in the system with role-based access control.

**Fields:**
- `id` - Primary key
- `username` - Unique username for login
- `password` - Hashed password
- `name` - Full name
- `email` - Email address
- `phone` - Phone number
- `role` - User role (admin, coordinator, teller, customer)
- `profile_photo_path` - Path to profile photo
- `is_active` - Whether the user account is active
- `location_id` - Foreign key to locations table
- `coordinator_id` - Foreign key to users table (for tellers assigned to coordinators)

**Relationships:**
- A coordinator has many tellers
- A teller belongs to one coordinator
- A user belongs to one location
- A teller has many bets
- A teller has one commission

### Game Types

The `game_types` table stores the different types of lottery games available.

**Fields:**
- `id` - Primary key
- `name` - Game type name (e.g., "Suertres 2 Digits")
- `code` - Short code (e.g., "S2", "S3", "D4")
- `is_active` - Whether the game type is active

**Relationships:**
- A game type has many draws
- A game type has many bets
- A game type has many winning amounts
- A game type has many bet ratios
- A game type has many low win numbers

### Draws

The `draws` table stores information about lottery draws.

**Fields:**
- `id` - Primary key
- `draw_date` - Date of the draw
- `draw_time` - Time of the draw
- `is_open` - Whether the draw is open for betting
- `is_active` - Whether the draw is active

**Relationships:**
- A draw has many bets
- A draw has one result
- A draw has many bet ratios
- A draw has many low win numbers

### Results

The `results` table stores the winning numbers for each draw.

**Fields:**
- `id` - Primary key
- `draw_id` - Foreign key to draws table
- `draw_date` - Date of the draw (redundant with draw relation)
- `draw_time` - Time of the draw (redundant with draw relation)
- `s2_winning_number` - Winning number for S2 game type
- `s3_winning_number` - Winning number for S3 game type
- `d4_winning_number` - Winning number for D4 game type

**Relationships:**
- A result belongs to one draw

### Bets

The `bets` table stores all bets placed in the system.

**Fields:**
- `id` - Primary key
- `draw_id` - Foreign key to draws table
- `game_type_id` - Foreign key to game_types table
- `teller_id` - Foreign key to users table (teller who placed the bet)
- `customer_id` - Foreign key to users table (optional customer)
- `location_id` - Foreign key to locations table
- `ticket_id` - Unique ticket identifier
- `bet_number` - The number bet on
- `amount` - Bet amount
- `is_claimed` - Whether the bet has been claimed
- `is_rejected` - Whether the bet was rejected
- `is_combination` - Whether the bet is a combination
- `bet_date` - Date and time the bet was placed
- `claimed_at` - Date and time the bet was claimed (added in migration)
- `winning_amount` - Amount won (added in migration)
- `d4_sub_selection` - For D4 game type sub-selection (added in migration)

**Relationships:**
- A bet belongs to one draw
- A bet belongs to one game type
- A bet belongs to one teller (user)
- A bet belongs to one customer (user, optional)
- A bet belongs to one location

### Bet Ratios

The `bet_ratios` table stores betting limits for specific numbers.

**Fields:**
- `id` - Primary key
- `draw_id` - Foreign key to draws table
- `game_type_id` - Foreign key to game_types table
- `bet_number` - The number with a limit
- `max_amount` - Maximum amount allowed for this number
- `user_id` - Foreign key to users table (who set the limit)
- `location_id` - Foreign key to locations table

**Relationships:**
- A bet ratio belongs to one draw
- A bet ratio belongs to one game type
- A bet ratio belongs to one user
- A bet ratio belongs to one location
- A bet ratio has many bet ratio audits

### Low Win Numbers

The `low_win_numbers` table stores numbers with reduced payouts.

**Fields:**
- `id` - Primary key
- `draw_id` - Foreign key to draws table
- `game_type_id` - Foreign key to game_types table
- `bet_number` - The number with reduced payout
- `winning_amount` - The reduced winning amount
- `user_id` - Foreign key to users table (who set the low win)
- `location_id` - Foreign key to locations table
- `reason` - Reason for the reduced payout

**Relationships:**
- A low win number belongs to one draw
- A low win number belongs to one game type
- A low win number belongs to one user
- A low win number belongs to one location

### Winning Amounts

The `winning_amounts` table stores standard winning amounts for each game type.

**Fields:**
- `id` - Primary key
- `location_id` - Foreign key to locations table
- `game_type_id` - Foreign key to game_types table
- `amount` - Bet amount
- `winning_amount` - Standard winning amount for this bet

**Relationships:**
- A winning amount belongs to one game type
- A winning amount belongs to one location

### Commissions

The `commissions` table stores commission rates for tellers.

**Fields:**
- `id` - Primary key
- `teller_id` - Foreign key to users table
- `rate` - Commission rate
- `bet_id` - Foreign key to bets table

**Relationships:**
- A commission belongs to one teller (user)
- A commission belongs to one bet
- A commission has many commission histories

### Schedules

The `schedules` table stores standard draw times.

**Fields:**
- `id` - Primary key
- `name` - Schedule name (e.g., "2:00 PM")
- `draw_time` - System time format
- `is_active` - Whether the schedule is active

**Relationships:**
- No direct relationships in current model

### Locations

The `locations` table stores branch locations.

**Fields:**
- `id` - Primary key
- `name` - Branch name
- `address` - Location address
- `is_active` - Whether the location is active (default: true)
- `created_at` - Timestamp when the record was created
- `updated_at` - Timestamp when the record was last updated

**Relationships:**
- A location has many users (`users` table)
- A location has many bets (`bets` table)
- A location has many bet ratios (`bet_ratios` table)
- A location has many low win numbers (`low_win_numbers` table)
- A location has many winning amounts (`winning_amounts` table)

## Key Relationships

1. **User Hierarchy**:
   - Coordinators manage multiple tellers
   - Tellers are assigned to one coordinator

2. **Draw System**:
   - Each draw has a date and time
   - Each draw can have multiple bets
   - Each draw has one result with winning numbers for different game types

3. **Betting System**:
   - Bets are associated with a specific draw, game type, and teller
   - Bet ratios limit the maximum amount for specific numbers
   - Low win numbers provide reduced payouts for specific numbers

4. **Commission System**:
   - Tellers earn commissions on bets
   - Commission rates are stored in the commissions table

## Entity Relationship Diagram

```
Users (1) ----< Bets (N)
  |
  |----< Users (Tellers) (N)
  |
  |----< Low Win Numbers (N)
  |
  |----< Bet Ratios (N)

Game Types (1) ----< Bets (N)
  |
  |----< Winning Amounts (N)
  |
  |----< Low Win Numbers (N)
  |
  |----< Bet Ratios (N)

Draws (1) ----< Bets (N)
  |
  |----< Results (1)
  |
  |----< Low Win Numbers (N)
  |
  |----< Bet Ratios (N)

Locations (1) ----< Users (N)
  |
  |----< Bets (N)
  |
  |----< Low Win Numbers (N)
  |
  |----< Bet Ratios (N)
  |
  |----< Winning Amounts (N)
```

This diagram shows the main entities and their relationships in the LuckyBet Admin system.

## Database Schema

This section provides the detailed database schema with column types for each table.

### users
```sql
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo_path` varchar(2048) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `current_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `coordinator_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_location_id_foreign` (`location_id`),
  KEY `users_coordinator_id_foreign` (`coordinator_id`),
  CONSTRAINT `users_coordinator_id_foreign` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### game_types
```sql
CREATE TABLE `game_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### draws
```sql
CREATE TABLE `draws` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `draw_date` date NOT NULL,
  `draw_time` time NOT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### results
```sql
CREATE TABLE `results` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `draw_id` bigint(20) UNSIGNED NOT NULL,
  `draw_date` date DEFAULT NULL,
  `draw_time` time DEFAULT NULL,
  `s2_winning_number` varchar(255) DEFAULT NULL,
  `s3_winning_number` varchar(255) DEFAULT NULL,
  `d4_winning_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `results_draw_id_foreign` (`draw_id`),
  CONSTRAINT `results_draw_id_foreign` FOREIGN KEY (`draw_id`) REFERENCES `draws` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### bets
```sql
CREATE TABLE `bets` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `draw_id` bigint(20) UNSIGNED NOT NULL,
  `game_type_id` bigint(20) UNSIGNED NOT NULL,
  `teller_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ticket_id` varchar(255) DEFAULT NULL,
  `bet_number` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `is_claimed` tinyint(1) NOT NULL DEFAULT 0,
  `is_rejected` tinyint(1) NOT NULL DEFAULT 0,
  `is_combination` tinyint(1) NOT NULL DEFAULT 0,
  `bet_date` datetime NOT NULL,
  `d4_sub_selection` varchar(255) DEFAULT NULL,
  `claimed_at` datetime DEFAULT NULL,
  `winning_amount` decimal(18,2) DEFAULT NULL,
  `commission` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bets_ticket_id_unique` (`ticket_id`),
  KEY `bets_draw_id_foreign` (`draw_id`),
  KEY `bets_game_type_id_foreign` (`game_type_id`),
  KEY `bets_teller_id_foreign` (`teller_id`),
  KEY `bets_customer_id_foreign` (`customer_id`),
  KEY `bets_location_id_foreign` (`location_id`),
  CONSTRAINT `bets_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `bets_draw_id_foreign` FOREIGN KEY (`draw_id`) REFERENCES `draws` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bets_game_type_id_foreign` FOREIGN KEY (`game_type_id`) REFERENCES `game_types` (`id`),
  CONSTRAINT `bets_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `bets_teller_id_foreign` FOREIGN KEY (`teller_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### bet_ratios
```sql
CREATE TABLE `bet_ratios` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `draw_id` bigint(20) UNSIGNED NOT NULL,
  `game_type_id` bigint(20) UNSIGNED NOT NULL,
  `bet_number` varchar(255) NOT NULL,
  `max_amount` decimal(10,2) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bet_ratios_draw_id_foreign` (`draw_id`),
  KEY `bet_ratios_game_type_id_foreign` (`game_type_id`),
  KEY `bet_ratios_user_id_foreign` (`user_id`),
  KEY `bet_ratios_location_id_foreign` (`location_id`),
  CONSTRAINT `bet_ratios_draw_id_foreign` FOREIGN KEY (`draw_id`) REFERENCES `draws` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bet_ratios_game_type_id_foreign` FOREIGN KEY (`game_type_id`) REFERENCES `game_types` (`id`),
  CONSTRAINT `bet_ratios_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bet_ratios_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### low_win_numbers
```sql
CREATE TABLE `low_win_numbers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `draw_id` bigint(20) UNSIGNED DEFAULT NULL,
  `game_type_id` bigint(20) UNSIGNED NOT NULL,
  `bet_number` varchar(255) NOT NULL,
  `winning_amount` decimal(18,2) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `low_win_numbers_draw_id_foreign` (`draw_id`),
  KEY `low_win_numbers_game_type_id_foreign` (`game_type_id`),
  KEY `low_win_numbers_user_id_foreign` (`user_id`),
  KEY `low_win_numbers_location_id_foreign` (`location_id`),
  CONSTRAINT `low_win_numbers_draw_id_foreign` FOREIGN KEY (`draw_id`) REFERENCES `draws` (`id`) ON DELETE CASCADE,
  CONSTRAINT `low_win_numbers_game_type_id_foreign` FOREIGN KEY (`game_type_id`) REFERENCES `game_types` (`id`),
  CONSTRAINT `low_win_numbers_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `low_win_numbers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### winning_amounts
```sql
CREATE TABLE `winning_amounts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `game_type_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `winning_amount` decimal(18,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `winning_amounts_location_id_foreign` (`location_id`),
  KEY `winning_amounts_game_type_id_foreign` (`game_type_id`),
  CONSTRAINT `winning_amounts_game_type_id_foreign` FOREIGN KEY (`game_type_id`) REFERENCES `game_types` (`id`),
  CONSTRAINT `winning_amounts_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### commissions
```sql
CREATE TABLE `commissions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `teller_id` bigint(20) UNSIGNED NOT NULL,
  `rate` decimal(8,2) NOT NULL,
  `bet_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commissions_teller_id_foreign` (`teller_id`),
  KEY `commissions_bet_id_foreign` (`bet_id`),
  CONSTRAINT `commissions_bet_id_foreign` FOREIGN KEY (`bet_id`) REFERENCES `bets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commissions_teller_id_foreign` FOREIGN KEY (`teller_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### schedules
```sql
CREATE TABLE `schedules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `draw_time` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### locations
```sql
CREATE TABLE `locations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Migration Files

The database structure is implemented through the following migration files:

1. **User-related Migrations**:
   - `0001_01_01_000000_create_users_table.php` - Creates the base users table
   - `0001_01_01_000004_add_foreign_keys_to_users_table.php` - Adds foreign key relationships
   - `2025_04_28_010752_add_two_factor_columns_to_users_table.php` - Adds two-factor authentication
   - `2025_05_06_031450_add_location_foreign_key_to_users_table.php` - Adds location relationship

2. **Core Game Migrations**:
   - `2025_05_06_031400_create_locations_table.php` - Creates locations (branches)
   - `2025_05_06_031500_create_game_types_table.php` - Creates game types (S2, S3, D4)
   - `2025_05_06_031600_create_schedules_table.php` - Creates draw schedules
   - `2025_05_06_031700_create_draws_table.php` - Creates draws with date and time
   - `2025_05_06_031800_create_bets_table.php` - Creates bets with relationships
   - `2025_05_06_031900_create_results_table.php` - Creates results with winning numbers
   - `2025_05_08_090500_add_is_active_to_draws_table.php` - Adds active flag to draws

3. **Betting System Migrations**:
   - `2025_05_06_032100_create_commissions_table.php` - Creates teller commissions
   - `2025_05_06_032500_create_bet_ratios_table.php` - Creates betting limits
   - `2025_05_06_032800_create_low_win_numbers_table.php` - Creates reduced payout numbers
   - `2025_05_14_200222_add_d4_sub_selection_to_bets_table.php` - Adds D4 sub-selection
   - `2025_05_14_202616_add_claimed_at_to_bets_table.php` - Adds claim timestamp
   - `2025_05_16_205104_create_winning_amounts_table.php` - Creates standard winning amounts
   - `2025_05_16_213824_add_winning_amount_to_bets_table.php` - Adds winning amount to bets

4. **Audit and History Migrations**:
   - `2025_05_21_141936_create_bet_ratio_audits_table.php` - Creates bet ratio audit trail
   - `2025_05_22_144730_create_commission_histories_table.php` - Creates commission history
   - `2025_05_22_144904_add_commission_to_bets_table.php` - Adds commission to bets
   - `2025_05_09_041939_update_results_foreign_key_to_cascade.php` - Updates foreign key to cascade delete

These migrations establish the database schema and relationships for the LuckyBet Admin system.
