<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitSimulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_profile_id',
        'reverse_goal_session_id',
        'baseline_revenue',
        'baseline_net_profit',
        'baseline_margin',
        'baseline_break_even_units',
        'leverage_zone',
        'adjustment_percentage',
        'projected_revenue',
        'projected_net_profit',
        'projected_margin',
        'projected_break_even_units',
        'profit_delta',
        'effort_score',
        'risk_multiplier',
        'leverage_impact_index',
        'primary_constraint',
        'constraint_severity',
        'mentor_focus_area',
        'risk_flags',
        'result_validation_flag',
        'logic_version'
    ];

    protected $casts = [
        'risk_flags' => 'array',
        'adjustment_percentage' => 'decimal:2',
        'projected_net_profit' => 'decimal:2',
        'profit_delta' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Relationship to BusinessProfile would go here if needed
    // public function businessProfile() { ... }
}
