<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZakatHolding extends Model
{
    use HasFactory;

    protected $fillable = [
        'zakat_calculation_id',
        'symbol',
        'strategy',
        'currency',
        'quantity',
        'unit_price',
        'market_value',
        'zakat_liable_amount',
        'zakat_due',
        'calculation_method'
    ];

    public function calculation()
    {
        return $this->belongsTo(ZakatCalculation::class, 'zakat_calculation_id');
    }
}
