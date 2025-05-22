<?php

namespace App\Models;

use App\Models\Commission;
use Illuminate\Database\Eloquent\Model;

class CommissionHistory extends Model
{
    protected $fillable = [
        'commission_id',
        'old_rate',
        'new_rate',
        'changed_by',
        'changed_at',
    ];
    
    public function commission()
    {
        return $this->belongsTo(Commission::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'changed_by');
    }
}
