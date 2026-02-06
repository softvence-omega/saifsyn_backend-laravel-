<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
    ];

    // Relation to sender
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Relation to receiver
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
