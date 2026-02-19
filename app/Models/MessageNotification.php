<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageNotification extends Model
{
    protected $fillable = [
        'user_id',
        'message_id',
        'is_read',
    ];

    // Notification belongs to a message
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    // Notification belongs to a user (admin)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
