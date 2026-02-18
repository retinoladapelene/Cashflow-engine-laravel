<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mode', // 'optimizer' or 'planner'
        'input_json',
        'baseline_json',
    ];

    protected $casts = [
        'input_json' => 'array',
        'baseline_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function simulationResults()
    {
        return $this->hasMany(SimulationResult::class, 'session_id');
    }
}
