<?php

namespace App\Services;

use App\Models\CustomerSession;
use Illuminate\Support\Facades\DB;

class CustomerSessionService
{
    /**
     * Get existing session or create new one
     */
    public function getOrCreate(string $phone): CustomerSession
    {
        return CustomerSession::firstOrCreate(
            ['phone' => $phone],
            [
                'step'       => CustomerSession::STEP_MAIN_MENU,
                'context'    => null,
                'product_id' => null,
            ]
        );
    }

    /**
     * Update session step safely
     */
    public function updateStep(
        CustomerSession $session,
        string $step,
        ?array $context = null,
        ?int $productId = null
    ): CustomerSession {

        $data = ['step' => $step];

        if (! is_null($context)) {
            $data['context'] = $context;
        }

        if (! is_null($productId)) {
            $data['product_id'] = $productId;
        }

        $session->update($data);

        return $session->fresh();
    }

    /**
     * Reset session completely
     */
    public function reset(CustomerSession $session): CustomerSession
    {
        return $session->resetSession();
    }

    /**
     * Safe reset with DB lock (Anti Race Condition)
     */
    public function resetWithLock(CustomerSession $session): CustomerSession
    {
        return DB::transaction(function () use ($session) {

            $locked = CustomerSession::where('id', $session->id)
                ->lockForUpdate()
                ->first();

            $locked->update([
                'step'       => CustomerSession::STEP_MAIN_MENU,
                'product_id' => null,
                'context'    => null,
            ]);

            return $locked->fresh();
        });
    }

  
    public function clearProductId(CustomerSession $session): CustomerSession
    {
        $session->update(['product_id' => null]);

        return $session->fresh();
    }
}
