<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBehaviorLog extends Model
{
    use HasFactory;

    protected $table = 'user_behavior_log';

    protected $fillable = [
        'user_id',
        'action_type',
        'selected_zone',
        'selected_level',
        'goal_status',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
