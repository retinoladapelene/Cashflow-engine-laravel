<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReverseGoalSession extends Model
{
    protected $fillable = [
        'user_id',
        'logic_version',
        'constraint_snapshot',
        'is_stable_plan',
        'business_model',
        'traffic_strategy',
        'target_profit',
        'timeline_days',
        'capital_available',
        'hours_per_day',
        'assumed_margin',
        'assumed_conversion',
        'assumed_cpc',
        'unit_net_profit',
        'required_units',
        'required_traffic',
        'required_ad_budget',
        'execution_load_ratio',
        'financial_score',
        'capital_score',
        'execution_score',
        'overall_score',
        'risk_level'
    ];

    protected $casts = [
        'constraint_snapshot' => 'array',
        'is_stable_plan' => 'boolean',
    ];

    public function simulations()
    {
        return $this->hasMany(ProfitSimulation::class);
    }
}
