@echo off
echo Removing old migration files...

del /f /q "database\migrations\2025_05_05_000001_restructure_draws_tables.php"
del /f /q "database\migrations\2025_05_05_000002_restructure_for_multiple_game_types.php"
del /f /q "database\migrations\2025_05_05_000003_create_game_types_table.php"
del /f /q "database\migrations\2025_05_05_000004_fix_draws_unique_constraint.php"
del /f /q "database\migrations\2025_05_05_000005_add_game_type_id_to_bets_table.php"
del /f /q "database\migrations\2025_05_05_000006_add_game_type_id_to_results_table.php"
del /f /q "database\migrations\2025_05_06_024500_add_is_active_to_game_types_table.php"
del /f /q "database\migrations\2025_05_06_025000_fix_game_types_table_structure.php"
del /f /q "database\migrations\0001_01_01_000003_create_locations_table.php"
del /f /q "database\migrations\2025_04_28_020133_create_schedules_table.php"
del /f /q "database\migrations\2025_04_28_020134_create_draws_table.php"
del /f /q "database\migrations\2025_04_28_020135_create_bets_table.php"
del /f /q "database\migrations\2025_04_28_020137_create_results_table.php"
del /f /q "database\migrations\2025_04_28_020138_create_claims_table.php"
del /f /q "database\migrations\2025_04_28_020139_create_commissions_table.php"
del /f /q "database\migrations\2025_04_28_020140_create_tally_sheets_table.php"

echo Old migration files have been removed.
echo You can now run "php artisan migrate:fresh --seed" to create the database with the new clean migrations.
