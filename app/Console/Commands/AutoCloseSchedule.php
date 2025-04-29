<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Schedule;
use Illuminate\Console\Command;

class AutoCloseSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-close-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close schedules if draw time has passed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Cron Job running at ". now());
        $now = Carbon::now()->format('H:i:s'); // current time in 24-hour format

        $schedules = Schedule::where('is_open', true)
            ->where('is_active', true)
            ->get();

        foreach ($schedules as $schedule) {
            if ($now >= $schedule->draw_time) {
                $schedule->is_open = false;
                $schedule->save();

                $this->info("Schedule {$schedule->name} auto-closed at {$now}.");
            }
        }   //
    }
}
