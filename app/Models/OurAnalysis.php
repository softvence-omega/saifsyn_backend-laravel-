<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OurAnalysis extends Model
{
    use SoftDeletes;

    protected $table = 'our_analyses'; // optional but good to be explicit

    protected $fillable = [
        'symbol',                        // Stock symbol
        'name',                          // Stock name
        'status',                        // COMPLIANT / NON_COMPLIANT / QUESTIONABLE
        'debt_to_market_cap_ratio',
        'securities_to_market_cap_ratio',
        'compliant_revenue',
        'non_compliant_revenue',
        'questionable_revenue',
        'recommendation',                // Admin analysis
        'note',                          // Admin note for user notification
    ];

    protected $casts = [
        'status' => 'string',
        'debt_to_market_cap_ratio' => 'decimal:4',
        'securities_to_market_cap_ratio' => 'float',
        'compliant_revenue' => 'float',
        'non_compliant_revenue' => 'float',
        'questionable_revenue' => 'float',
    ];
}
