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
        'teller_id',        // References users.id
        'location_id',      // References locations.id
        'sheet_date',       // Date of the tally sheet
        'total_sales',      // Total sales amount
        'total_claims',     // Total claims amount
        'total_commission', // Total commission amount
        'net_amount'        // total_sales - claims - commissions
    ];
    
    protected $casts = [
        'sheet_date' => 'date',
        'total_sales' => 'decimal:2',
        'total_claims' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'net_amount' => 'decimal:2',
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
