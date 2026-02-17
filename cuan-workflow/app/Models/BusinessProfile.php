<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'variable_costs' => 'decimal:2',
        'fixed_costs' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'ad_spend' => 'decimal:2',
        'target_revenue' => 'decimal:2',
        'available_cash' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
