<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;

   protected $fillable = ['user_id','title','amount','interest_rate','repayment_period','start_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
