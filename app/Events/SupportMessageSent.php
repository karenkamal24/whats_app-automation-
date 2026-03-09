<?php

namespace App\Events;

use App\Models\SupportMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    public function __construct(
        public SupportMessage $message,
        public int $conversationId,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("support.{$this->conversationId}")
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'sender'          => $this->message->sender,
            'message'         => $this->message->message,
            'created_at'      => $this->message->created_at->format('H:i'),
            'conversation_id' => $this->conversationId,
        ];
    }
}
