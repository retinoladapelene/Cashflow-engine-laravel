<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimulationResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'scenario_json',
        'sensitivity_json',
        'break_even_json',
        'upsell_json',
    ];

    protected $casts = [
        'scenario_json' => 'array',
        'sensitivity_json' => 'array',
        'break_even_json' => 'array',
        'upsell_json' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(MentorSession::class, 'session_id');
    }
}
