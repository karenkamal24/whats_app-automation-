<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Events\SupportMessageSent;

class SupportMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender',
        'message',
        'is_read',
    ];

    protected static function booted()
    {
        static::created(function ($message) {
            broadcast(new SupportMessageSent(
                $message,
                $message->conversation_id
            ));
        });
    }

    public function conversation()
    {
        return $this->belongsTo(SupportConversation::class);
    }
}
