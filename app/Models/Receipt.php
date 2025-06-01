<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'teller_id',
        'location_id',
        'receipt_date',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Calculate the total amount of all bets in this receipt
     * 
     * @return float
     */
    public function calculateTotalAmount()
    {
        return $this->bets()->sum('amount');
    }

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::creating(function ($receipt) {
            // Only assign ticket_id if receipt is being placed/finalized
            if (empty($receipt->ticket_id) && $receipt->status === 'placed') {
                $prefix = 'LB-';
                $date = now()->format('ymd-Hi'); // yymmdd-hhmm
                $rand = strtoupper(Str::random(4)); // A7D4
                $receipt->ticket_id = $prefix . $date . '-' . $rand;
                
                // Set receipt date if not already set
                if (empty($receipt->receipt_date)) {
                    $receipt->receipt_date = now()->toDateString();
                }
                
                // Set total amount if not already set
                if (empty($receipt->total_amount)) {
                    $receipt->total_amount = $receipt->calculateTotalAmount();
                }
            }
        });

        static::updating(function ($receipt) {
            if (empty($receipt->ticket_id) && $receipt->status === 'placed') {
                $prefix = 'LB-';
                $date = now()->format('ymd-Hi');
                $rand = strtoupper(Str::random(4));
                $receipt->ticket_id = $prefix . $date . '-' . $rand;
                
                // Set receipt date if not already set
                if (empty($receipt->receipt_date)) {
                    $receipt->receipt_date = now()->toDateString();
                }
                
                // Set total amount if not already set
                if (empty($receipt->total_amount)) {
                    $receipt->total_amount = $receipt->calculateTotalAmount();
                }
            }
        });
    }
}
