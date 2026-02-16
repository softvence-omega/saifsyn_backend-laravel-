<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZakatCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'zakat_liable_amount',
        'zakat_due',
        'currency'
    ];

    public function holdings()
    {
        return $this->hasMany(ZakatHolding::class);
    }
}
