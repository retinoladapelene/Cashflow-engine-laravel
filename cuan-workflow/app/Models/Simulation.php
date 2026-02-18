<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'input_data' => 'array',
        'result_data' => 'array',
        'health_score' => 'array',
        'generated_tags' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roadmap()
    {
        return $this->hasOne(Roadmap::class);
    }
}
