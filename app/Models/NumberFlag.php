<?php

namespace App\Models;

use App\Models\Location;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NumberFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'schedule_id',
        'date',
        'location_id',
        'type',
        'is_active',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
