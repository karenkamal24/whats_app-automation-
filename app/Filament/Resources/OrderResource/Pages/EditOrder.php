<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Services\OrderService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /*
    |------------------------------------------------------------------
    | When the admin saves changes from the Edit page
    | we use OrderService to automatically send the WhatsApp message
    |------------------------------------------------------------------
    */
    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // If the status has changed, use the service
        if (isset($data['status']) && $record->status->value !== $data['status']) {
            $orderService = app(OrderService::class);

            $orderService->updateStatus(
                $record,
                OrderStatus::from($data['status'])
            );

            // Remove status from data so it doesn't get saved twice
            unset($data['status']);
        }

        // Save any other updated fields
        if (! empty($data)) {
            $record->update($data);
        }

        Notification::make()
            ->title('Changes saved successfully')
            ->success()
            ->send();

        return $record->fresh();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}



