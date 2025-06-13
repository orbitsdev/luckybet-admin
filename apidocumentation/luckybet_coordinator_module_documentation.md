
# 📊 LuckyBet Coordinator Module Documentation

## Overview

The **Coordinator Module** is a set of Livewire components designed for coordinators to monitor and analyze teller performance within their assigned location.

This module is fully **read-only** — coordinators **do not approve**, **edit**, or **delete** any record. All data is scoped to the **coordinator’s assigned tellers and location**.

---

## ✅ Features Included

### 1. Dashboard Summary
A high-level view of:
- Total sales, hits, gross
- Draws scheduled today
- Missing draw results
- Quick summaries per teller

### 2. Sales Summary by Tellers
- Lists all tellers under a coordinator
- Displays sales, hits, gross per teller
- Modal for quick-view per teller
- Button to view teller’s bets

### 3. Teller Bets Breakdown
- Full table of all bets placed by a teller
- Filters by game type, draw time, status, ticket ID / number
- Display of winning amount, commission, claimed status

---

## 📁 File Locations

All coordinator-level components are in:

```
app/Livewire/Reports/Coordinator/
resources/views/livewire/reports/coordinator/
```

---

## 📌 Component Breakdown

### `CoordinatorSalesSummary.php`
- Entry point to view teller performance for today (or selected date)
- Sales stats are based on complete draws only
- Supports admin view across coordinators or coordinator view of their own

### `CoordinatorTellerSalesSummary.php`
- View sales per draw per teller
- Shows individual draw performance
- Indicates missing draw results for clarity

### `TellerBetsReport.php`
- Full breakdown of all bets for a teller on a selected day
- Grouped by draw time
- Includes status, hits, commissions
- Supports filters: status, game type, draw time

---

## 🖼 UI Notes

- Blade layout uses `<x-admin>` for consistency
- TailwindCSS and Filament components for uniform design
- Warning banner if draw results are incomplete
- `@foreach` loops with `number_format()` ensure currency display

---

## ✅ Developer Setup

### Artisan Commands

To create any new Livewire coordinator pages:
```bash
php artisan make:livewire Reports/Coordinator/ComponentName
```

### Access Control
Ensure each coordinator can only view:
```php
User::where('role', 'teller')->where('coordinator_id', auth()->id())->get();
```

### Blade Styling Tips
- Use `bg-white p-4 rounded-lg shadow-md` for cards
- Use `text-sm font-medium text-gray-500` for labels
- Use conditional classes for colors:
  ```blade
  {{ $value >= 0 ? 'text-green-600' : 'text-red-600' }}
  ```

---

## 📈 Future Ideas

- Export to PDF/CSV
- Toggle between gross/net commission
- Bar/line charts for bet trends

---

## 💡 Summary

The Coordinator Module equips middle-tier managers to oversee teller activity, ensuring transparency in reporting and real-time monitoring.

All components follow best practices in:
- Data scoping per user
- Efficient Livewire updates
- Clear UI and mobile responsiveness

---

Prepared for development team — June 2025
