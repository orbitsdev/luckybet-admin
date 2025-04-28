<?php

namespace App\Models;

use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TallySheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'teller_id', 'location_id', 'sheet_date', 'total_sales', 'total_claims', 'total_commission', 'net_amount'
    ];

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
