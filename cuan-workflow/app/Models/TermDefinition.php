<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'term_key',
        'short_text',
        'long_text',
        'contextual_template',
        'trigger_rules',
        'context_type'
    ];

    protected $casts = [
        'trigger_rules' => 'array',
    ];
}
