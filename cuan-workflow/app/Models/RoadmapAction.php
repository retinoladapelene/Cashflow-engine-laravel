<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapAction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_completed' => 'boolean',
        'tool_recommendation' => 'array',
    ];

    public function step()
    {
        return $this->belongsTo(RoadmapStep::class, 'step_id');
    }
}
