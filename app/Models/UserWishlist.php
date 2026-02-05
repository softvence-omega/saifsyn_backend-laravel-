<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWishlist extends Model
{
    use SoftDeletes;

    protected $table = 'user_wishlist';

    protected $fillable = [
        'user_id',
        'stock_symbol',
    ];

    // Relation with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
