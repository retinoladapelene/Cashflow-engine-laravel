<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'selling_price',
        'variable_costs',
        'fixed_costs',
        'traffic',
        'conversion_rate',
        'ad_spend',
        'target_revenue',
        'available_cash',
        'max_capacity',
        'currency',
    ];

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
