<?php

namespace App\Filament\Pages;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\WhatsAppNotificationService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SupportChat extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel ='support chat';
    protected static ?string $navigationGroup = 'support';
    protected static string  $view            = 'filament.pages.support-chat';

    public ?int    $activeConversationId = null;
    public ?string $replyMessage         = null;

    public function mount(): void
    {
        if (! $this->activeConversationId) {
            $this->activeConversationId = SupportConversation::whereIn('status', ['open', 'in_progress'])
                ->latest()
                ->value('id');
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SupportConversation::query()
                    ->withCount([
                        'messages as unread_count' => fn (Builder $query) => $query
                            ->where('sender', 'customer')
                            ->where('is_read', false),
                    ])
            )
            ->columns([
                TextColumn::make('customer_phone')
                    ->label('رقم العميل')
                    ->searchable(),

                TextColumn::make('customer_name')
                    ->label('الاسم')
                    ->default('-'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'open'        => '🟢 مفتوحة',
                        'in_progress' => '🟡 جاري المتابعة',
                        'closed'      => '⚫ مغلقة',
                        default       => $state,
                    }),

                TextColumn::make('unread_count')
                    ->label('غير مقروءة')
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'danger' : 'gray'),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->since(),
            ])
            ->actions([
                Action::make('open')
                    ->label('فتح')
                    ->color('primary')
                    ->action(function (SupportConversation $record) {
                        $this->activeConversationId = $record->id;

                        $record->messages()
                            ->where('sender', 'customer')
                            ->where('is_read', false)
                            ->update(['is_read' => true]);

                        if ($record->status === 'open') {
                            $record->update([
                                'status'      => 'in_progress',
                                'assigned_to' => Auth::id(),
                            ]);
                        }
                    }),

                Action::make('close')
                    ->label('إغلاق')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function (SupportConversation $record) {
                        $record->update(['status' => 'closed']);

                        app(WhatsAppNotificationService::class)->sendMessage(
                            $record->customer_phone,
                            "✅ تم إغلاق المحادثة.\n\nشكراً لتواصلك معنا 🙏\n\nاكتب أي رسالة للرجوع للقائمة."
                        );

                        if ($this->activeConversationId === $record->id) {
                            $this->activeConversationId = null;
                        }

                        Notification::make()
                            ->title('تم إغلاق المحادثة')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('replyMessage')
                ->label('رسالة للعميل')
                ->rows(3)
                ->required()
                ->placeholder('اكتب ردك هنا...'),
        ];
    }

    public function sendReply(WhatsAppNotificationService $whatsApp): void
    {
        $this->validate([
            'replyMessage' => ['required', 'string', 'min:1'],
        ]);

        if (! $this->activeConversationId) {
            Notification::make()
                ->title('اختر محادثة أولاً')
                ->danger()
                ->send();
            return;
        }

        $conversation = SupportConversation::find($this->activeConversationId);
        if (! $conversation) return;

        SupportMessage::create([
            'conversation_id' => $conversation->id,
            'sender'          => 'agent',
            'message'         => $this->replyMessage,
            'is_read'         => true,
        ]);

        $whatsApp->sendMessage(
            $conversation->customer_phone,
            "👨‍💼 رد الموظف:\n\n{$this->replyMessage}"
        );

        if ($conversation->status === 'open') {
            $conversation->update(['status' => 'in_progress']);
        }

        $this->replyMessage = null;

        Notification::make()
            ->title('تم إرسال الرسالة ✅')
            ->success()
            ->send();
    }

    public function getActiveConversationProperty(): ?SupportConversation
    {
        if (! $this->activeConversationId) return null;

        return SupportConversation::with('messages')->find($this->activeConversationId);
    }

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

    public function render(): View
    {
        return view(static::$view, [
            'activeConversation' => $this->activeConversation,
        ]);
    }
}
