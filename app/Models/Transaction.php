<?php

namespace App\Models;

use App\Models\Draw;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Draw (game/draw schedule)
    public function draw()
    {
        return $this->belongsTo(Draw::class);
    }
}
