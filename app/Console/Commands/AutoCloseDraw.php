<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Draw;
use Illuminate\Console\Command;

class AutoCloseDraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-close-draw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close draws when their draw_time has passed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now()->format('H:i:s');

        $draws = Draw::where('is_open', true)
            ->where('draw_date', today())
            ->get();

        foreach ($draws as $draw) {
            if ($now >= $draw->draw_time) {
                $draw->is_open = false;
                $draw->save();

                $this->info("Auto-closed Draw: Type {$draw->type} at {$draw->draw_time}");
            }
        }
    }
}
