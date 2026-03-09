<?php

namespace App\Console\Commands;

use App\Services\WhatsAppNotificationService;
use Illuminate\Console\Command;

class WhatsAppTestMessage extends Command
{
    protected $signature = 'whatsapp:test
        {phone : Destination phone in international format (e.g. 2012xxxxxxx)}
        {message? : Message text (optional)}';

    protected $description = 'Send a test WhatsApp message via n8n webhook';

    public function __construct(
        private readonly WhatsAppNotificationService $notifier,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $phone = (string) $this->argument('phone');
        $message = (string) ($this->argument('message') ?: 'Test message from Laravel ✅');

        $this->info("📨 Sending test message to {$phone}...");
        $this->notifier->sendText($phone, $message);
        $this->info('✅ Done. Check n8n executions and WhatsApp device.');

        return self::SUCCESS;
    }
}





