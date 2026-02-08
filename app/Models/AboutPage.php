<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AboutPage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'description',
        'our_mission',
        'our_vision',
        'video',
    ];

    protected $casts = [
        'our_mission' => 'array',
        'our_vision' => 'array',
    ];
}
