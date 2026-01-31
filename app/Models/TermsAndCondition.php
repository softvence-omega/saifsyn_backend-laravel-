<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsAndCondition extends Model
{
    use HasFactory;

    protected $table = 'terms_and_conditions';

    protected $fillable = [
        'content',
        'is_active',
    ];

    protected $casts = [
        'content'   => 'array',
        'is_active' => 'boolean',
    ];
}
