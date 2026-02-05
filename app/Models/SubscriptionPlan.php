<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'price',
        'features',
        'duration_type',
        'duration_value',
        'is_popular',
        'status',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'float',
        'status' => 'boolean',
        'is_popular' => 'boolean',
        'duration_value' => 'integer',
    ];
}
