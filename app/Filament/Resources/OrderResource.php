<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 2;

    /* ------------------------------------------------------------------ */
    /*  Form                                                               */
    /* ------------------------------------------------------------------ */

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Details')
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->relationship('product', 'name')
                        ->disabled(),

                    Forms\Components\TextInput::make('customer_phone')
                        ->disabled(),

                    Forms\Components\TextInput::make('customer_name')
                        ->disabled(),

                    Forms\Components\TextInput::make('amount')
                        ->prefix('EGP')
                        ->disabled(),

                    Forms\Components\Select::make('payment_method')
                        ->options(collect(PaymentMethod::cases())
                            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                            ->toArray()
                        )
                        ->disabled(),

                    Forms\Components\TextInput::make('payment_reference')
                        ->disabled(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Shipping Details')
                ->schema([
                    Forms\Components\TextInput::make('governorate')->disabled(),
                    Forms\Components\TextInput::make('city')->disabled(),
                    Forms\Components\TextInput::make('street')->disabled(),
                    Forms\Components\Textarea::make('notes')->disabled(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Order Status')
                ->schema([
                    /*
                    |------------------------------------------------------
                    | Only admin can change to allowed statuses
                    |------------------------------------------------------
                    */
                    Forms\Components\Select::make('status')
                        ->options(
                            collect(OrderStatus::adminAllowed())
                                ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                                ->toArray()
                        )
                        ->required()
                        ->live(),
                ]),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Table                                                              */
    /* ------------------------------------------------------------------ */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn(PaymentMethod $state) => $state->label())
                    ->color(fn(PaymentMethod $state) => match($state) {
                        PaymentMethod::CASH => 'info',
                        PaymentMethod::VISA => 'primary',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn(OrderStatus $state) => $state->label())
                    ->color(fn(OrderStatus $state) => match($state) {
                        OrderStatus::PENDING         => 'warning',
                        OrderStatus::PENDING_PAYMENT => 'info',
                        OrderStatus::PAID            => 'success',
                        OrderStatus::PROCESSING      => 'primary',
                        OrderStatus::SHIPPED         => 'primary',
                        OrderStatus::DELIVERED       => 'success',
                        OrderStatus::CANCELLED       => 'danger',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(
                        collect(OrderStatus::cases())
                            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                            ->toArray()
                    ),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(
                        collect(PaymentMethod::cases())
                            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                            ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                /*
                |----------------------------------------------------------
                | Action to update status and automatically send WhatsApp
                |----------------------------------------------------------
                */
                Tables\Actions\Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->options(
                                collect(OrderStatus::adminAllowed())
                                    ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                                    ->toArray()
                            )
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $orderService = app(OrderService::class);

                        $orderService->updateStatus(
                            $record,
                            OrderStatus::from($data['status'])
                        );

                        Notification::make()
                            ->title('Order status updated')
                            ->body("Order #{$record->id} status changed to " . OrderStatus::from($data['status'])->label())
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                /*
                |----------------------------------------------------------
                | Bulk action to update multiple orders at once
                |----------------------------------------------------------
                */
                Tables\Actions\BulkAction::make('bulkUpdateStatus')
                    ->label('Update Selected Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->options(
                                collect(OrderStatus::adminAllowed())
                                    ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                                    ->toArray()
                            )
                            ->required(),
                    ])
                    ->action(function ($records, array $data): void {
                        $orderService = app(OrderService::class);
                        $newStatus    = OrderStatus::from($data['status']);

                        foreach ($records as $record) {
                            $orderService->updateStatus($record, $newStatus);
                        }

                        Notification::make()
                            ->title('Orders status updated successfully')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Pages                                                              */
    /* ------------------------------------------------------------------ */

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit'  => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}



