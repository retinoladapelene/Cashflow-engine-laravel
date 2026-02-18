<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTermFamiliarity extends Model
{
    use HasFactory;

    protected $table = 'user_term_familiarity';

    protected $fillable = [
        'user_id',
        'term_key',
        'click_count',
        'familiarity_score',
        'last_interaction_at'
    ];

    protected $casts = [
        'last_interaction_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
