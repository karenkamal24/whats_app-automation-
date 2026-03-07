<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSession extends Model
{
    protected $fillable = [
        'phone',
        'step',
        'product_id',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    /* ---------------- Conversation Steps ---------------- */

    public const STEP_MAIN_MENU                   = 'main_menu';
    public const STEP_AWAITING_CATEGORY_SELECTION = 'awaiting_category_selection';
    public const STEP_AWAITING_PRODUCT_SELECTION  = 'awaiting_product_selection';
    public const STEP_AWAITING_PAYMENT_METHOD     = 'awaiting_payment_method';

    // ====== Steps جديدة ======
    public const STEP_AWAITING_NAME               = 'awaiting_name';
    public const STEP_AWAITING_QUANTITY           = 'awaiting_quantity';
    public const STEP_AWAITING_GOVERNORATE        = 'awaiting_governorate';
    public const STEP_AWAITING_CITY               = 'awaiting_city';
    public const STEP_AWAITING_STREET             = 'awaiting_street';
    public const STEP_AWAITING_NOTES              = 'awaiting_notes';
    public const STEP_AWAITING_CONFIRM            = 'awaiting_confirm';

    /* ---------------- Relationships ---------------- */
    public const STEP_AWAITING_ORDER_TRACKING = 'awaiting_order_tracking';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /* ---------------- Helpers ---------------- */

    public function resetSession(): self
    {
        $this->update([
            'step'       => self::STEP_MAIN_MENU,
            'product_id' => null,
            'context'    => null,
        ]);

        return $this->fresh();
    }
}
