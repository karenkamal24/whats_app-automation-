<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportConversation extends Model
{
    protected $fillable = [
        'customer_phone',
        'customer_name',
        'status',
        'assigned_to',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'conversation_id')->orderBy('created_at');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function unreadCount(): int
    {
        return $this->messages()->where('sender', 'customer')->where('is_read', false)->count();
    }
}
