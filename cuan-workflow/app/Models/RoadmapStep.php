<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapStep extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function roadmap()
    {
        return $this->belongsTo(Roadmap::class);
    }

    public function actions()
    {
        return $this->hasMany(RoadmapAction::class, 'step_id');
    }
}
