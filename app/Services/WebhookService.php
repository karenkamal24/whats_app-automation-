<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Category;
use App\Models\CustomerSession;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class WebhookService
{
    public function __construct(
        private readonly CustomerSessionService $sessionService,
        private readonly ProductService         $productService,
        private readonly OrderService           $orderService,
        private readonly PaymobService          $paymobService,
    ) {}

    public function handle(string $phone, string $message, ?string $intent = null): array
    {
        $message = trim($message);
        $session = $this->sessionService->getOrCreate($phone);

        if ($this->isResetCommand($message)) {
            return $this->handleReset($session);
        }

        $isInFlow = ! in_array($session->step, [
            CustomerSession::STEP_MAIN_MENU,
            CustomerSession::STEP_AWAITING_CATEGORY_SELECTION,
            CustomerSession::STEP_AWAITING_PRODUCT_SELECTION,
        ]);

        if (! $isInFlow && $intent && $intent !== 'other' && ! ctype_digit($message)) {
            return match ($intent) {
                'greeting'          => $this->handleMainMenu($session),
                'browse_categories' => $this->handleBrowseCategories($session),
                'search_product'    => $this->handleProductSearch($session, $message),
                default             => $this->handleMainMenu($session),
            };
        }

        return match ($session->step) {
            CustomerSession::STEP_MAIN_MENU
                => $this->handleMainMenuInput($session, $message),
            CustomerSession::STEP_AWAITING_CATEGORY_SELECTION
                => $this->handleCategorySelection($session, $message),
            CustomerSession::STEP_AWAITING_PRODUCT_SELECTION
                => $this->handleProductSelection($session, $message),
            CustomerSession::STEP_AWAITING_PAYMENT_METHOD
                => $this->handlePaymentMethod($session, $message),
            CustomerSession::STEP_AWAITING_NAME
                => $this->handleName($session, $message),
            CustomerSession::STEP_AWAITING_QUANTITY
                => $this->handleQuantity($session, $message),
            CustomerSession::STEP_AWAITING_GOVERNORATE
                => $this->handleGovernorate($session, $message),
            CustomerSession::STEP_AWAITING_CITY
                => $this->handleCity($session, $message),
            CustomerSession::STEP_AWAITING_STREET
                => $this->handleStreet($session, $message),
            CustomerSession::STEP_AWAITING_NOTES
                => $this->handleNotes($session, $message),
            CustomerSession::STEP_AWAITING_CONFIRM
                => $this->handleConfirm($session, $message),
            CustomerSession::STEP_AWAITING_ORDER_TRACKING
                => $this->handleOrderTracking($session, $message),
            default
                => $this->handleMainMenu($session),
        };
    }

    /* ================= MAIN MENU ================= */

    private function handleMainMenuInput(CustomerSession $session, string $message): array
    {
        if ($message === '1') return $this->handleBrowseCategories($session);
        if ($message === '2') return $this->handleShowOrderTracking($session);
        if (! ctype_digit($message)) return $this->handleProductSearch($session, $message);
        return $this->handleMainMenu($session);
    }

    private function handleMainMenu(CustomerSession $session): array
    {
        $this->sessionService->updateStep(
            $session,
            CustomerSession::STEP_MAIN_MENU,
            context: null,
            productId: null
        );

        return $this->reply(
            "👋 أهلاً بيك في Smart Store\n\n" .
            "1️⃣ تصفح الأقسام\n" .
            "2️⃣ تابع طلباتك 📦\n\n" .
            "أو اكتب اسم المنتج مباشرة 🔎"
        );
    }

    /* ================= ORDER TRACKING ================= */

    private function handleShowOrderTracking(CustomerSession $session): array
    {
        /*
        |--------------------------------------------------------------
        | أول ما يضغط 2، نجيب طلباته الأخيرة مباشرة من غير ما يكتب رقم
        |--------------------------------------------------------------
        */
        $orders = Order::where('customer_phone', $session->phone)
            ->whereNotIn('status', [OrderStatus::CANCELLED])
            ->with('product')
            ->latest()
            ->take(5)
            ->get();

        if ($orders->isEmpty()) {
            return $this->reply("📭 مفيش طلبات نشطة حالياً.\n\nاكتب اسم منتج للبحث عنه 🔎");
        }

        if ($orders->count() === 1) {
            // لو طلب واحد بس، اعرضه مباشرة
            return $this->showOrderDetails($orders->first());
        }

        // لو أكتر من طلب، اعرض قايمة
        $lines = ["📦 طلباتك الأخيرة:\n"];

        foreach ($orders as $i => $order) {
            $num          = $i + 1;
            $statusLabel  = $order->status->label();
            $productName  = $order->product?->name ?? 'منتج';
            $lines[]      = "{$num}️⃣ طلب #{$order->id} — {$productName}";
            $lines[]      = "   الحالة: {$statusLabel}\n";
        }

        $lines[] = "رد برقم الطلب عشان تشوف التفاصيل 👇";

        $this->sessionService->updateStep(
            $session,
            CustomerSession::STEP_AWAITING_ORDER_TRACKING,
            context: ['order_ids' => $orders->pluck('id')->toArray()]
        );

        return $this->reply(implode("\n", $lines));
    }

    private function handleOrderTracking(CustomerSession $session, string $message): array
    {
        $orderIds = data_get($session->context, 'order_ids', []);

        if (! ctype_digit($message)) {
            return $this->reply("⚠️ من فضلك اختار رقم الطلب من القايمة.");
        }

        $index = (int) $message - 1;

        if (! isset($orderIds[$index])) {
            return $this->reply("⚠️ رقم غير صحيح، اختار من القايمة.");
        }

        $order = Order::with('product')->find($orderIds[$index]);

        if (! $order) {
            return $this->handleMainMenu($session);
        }

        $this->sessionService->updateStep(
            $session,
            CustomerSession::STEP_MAIN_MENU,
            context: null
        );

        return $this->showOrderDetails($order);
    }

    private function showOrderDetails(Order $order): array
    {
        $statusLabel  = $order->status->label();
        $productName  = $order->product?->name ?? 'منتج';
        $paymentLabel = $order->payment_method->label();

        /*
        |--------------------------------------------------------------
        | Progress bar بسيط حسب الحالة
        |--------------------------------------------------------------
        */
        $progress = match ($order->status) {
            OrderStatus::PENDING, OrderStatus::PENDING_PAYMENT => "⬜⬜⬜⬜ في الانتظار",
            OrderStatus::PAID                                  => "🟩⬜⬜⬜ تم الدفع",
            OrderStatus::PROCESSING                            => "🟩🟩⬜⬜ قيد التجهيز",
            OrderStatus::SHIPPED                               => "🟩🟩🟩⬜ في الطريق",
            OrderStatus::DELIVERED                             => "🟩🟩🟩🟩 تم التسليم ✅",
            OrderStatus::CANCELLED                             => "❌ ملغي",
            default                                            => $statusLabel,
        };

        return $this->reply(
            "📦 تفاصيل الطلب #{$order->id}\n\n" .
            "🛍 المنتج: {$productName}\n" .
            "🔢 الكمية: {$order->quantity}\n" .
            "💰 المبلغ: {$order->amount} EGP\n" .
            "💳 الدفع: {$paymentLabel}\n\n" .
            "📍 الحالة:\n{$progress}\n\n" .
            "📅 تاريخ الطلب: {$order->created_at->format('d/m/Y')}\n\n" .
            "للرجوع للقائمة الرئيسية اكتب *رجوع*"
        );
    }

    /* ================= CATEGORIES ================= */

    private function handleBrowseCategories(CustomerSession $session): array
    {
        $categories = Category::all();

        if ($categories->isEmpty()) return $this->reply("😕 لا يوجد أقسام حالياً.");

        $lines = ["📂 الأقسام المتاحة:\n"];
        foreach ($categories as $i => $category) {
            $lines[] = ($i + 1) . "️⃣ {$category->name}";
        }
        $lines[] = "\nرد برقم القسم 👇";

        $this->sessionService->updateStep($session, CustomerSession::STEP_AWAITING_CATEGORY_SELECTION, context: null);

        return $this->reply(implode("\n", $lines));
    }

    private function handleCategorySelection(CustomerSession $session, string $message): array
    {
        if (! ctype_digit($message)) return $this->reply("⚠️ من فضلك اختاري رقم القسم.");

        $categories = Category::all();
        $index      = (int) $message - 1;

        if (! isset($categories[$index])) return $this->reply("⚠️ رقم غير صحيح.");

        $category = $categories[$index];
        $products = $category->products()->active()->inStock()->take(10)->get();

        if ($products->isEmpty()) return $this->reply("😕 لا يوجد منتجات في هذا القسم.");

        return $this->showProductList($session, $products, "🛍 منتجات {$category->name}:");
    }

    /* ================= PRODUCTS ================= */

    private function handleProductSearch(CustomerSession $session, string $message): array
    {
        $products = Product::active()->inStock()->search($message)->take(10)->get();

        if ($products->isEmpty()) return $this->reply("😕 ملقيناش منتجات مطابقة لبحثك.");

        return $this->showProductList($session, $products, "🔎 نتائج البحث:");
    }

    private function showProductList(CustomerSession $session, $products, string $title): array
    {
        $lines = ["{$title}\n"];
        $map   = [];

        foreach ($products as $i => $product) {
            $lines[] = ($i + 1) . "️⃣ {$product->name}";
            $lines[] = "💰 {$product->price} EGP\n";
            $map[]   = ['id' => $product->id];
        }

        $lines[] = "رد برقم المنتج 👇";

        $this->sessionService->updateStep($session, CustomerSession::STEP_AWAITING_PRODUCT_SELECTION, context: ['search_results' => $map]);

        return $this->reply(implode("\n", $lines));
    }

    private function handleProductSelection(CustomerSession $session, string $message): array
    {
        $results = data_get($session->context, 'search_results', []);

        if (! ctype_digit($message)) return $this->reply("⚠️ من فضلك اختاري رقم المنتج.");

        $index = (int) $message - 1;

        if (! isset($results[$index])) return $this->reply("⚠️ رقم غير صحيح.");

        $product = $this->productService->findAvailable($results[$index]['id']);

        if (! $product) return $this->handleMainMenu($session);

        $this->sessionService->updateStep($session, CustomerSession::STEP_AWAITING_PAYMENT_METHOD, context: null, productId: $product->id);

        return $this->reply(
            "✨ {$product->name}\n" .
            "💰 السعر: {$product->price} EGP\n\n" .
            "💳 طريقة الدفع:\n" .
            "1️⃣ كاش\n" .
            "2️⃣ فيزا\n\n" .
            "رد بـ 1 أو 2 👇"
        );
    }

    /* ================= PAYMENT METHOD ================= */

    private function handlePaymentMethod(CustomerSession $session, string $message): array
    {
        if (! $session->product_id) return $this->handleMainMenu($session);

        $method = $this->parsePaymentMethod($message);

        if (! $method) return $this->reply("⚠️ من فضلك اختاري 1 أو 2.");

        $this->sessionService->updateStep($session, CustomerSession::STEP_AWAITING_NAME, context: ['payment_method' => $method->value], productId: $session->product_id);

        return $this->reply("👤 اكتب اسمك الكامل من فضلك:");
    }

    /* ================= COLLECT ORDER DETAILS ================= */

    private function handleName(CustomerSession $session, string $message): array
    {
        if (mb_strlen($message) < 3) return $this->reply("⚠️ من فضلك اكتب اسمك كامل (3 حروف على الأقل).");

        $context         = $session->context ?? [];
        $context['name'] = $message;

        $this->sessionService->updateStep($session, CustomerSession::STEP_AWAITING_QUANTITY, context: $context, productId: $session->product_id);

        $product = Product::find($session->product_id);

        return $this->reply(
            "🛒 كام قطعة عايز تطلب؟\n" .
            "📦 المتاح: {$product->stock} قطعة\n\n" .
            "اكتب الكمية 👇"
        );
    }

    private function handleQuantity(CustomerSession $session, string $message): array
    {
        if (! ctype_digit($message) || (int) $message < 1) return $this->reply("⚠️ من فضلك اكتب كمية صحيحة.");

        $quantity = (int) $message;
        $product  = Product::find($session->product_id);

        if (! $product || $product->stock < $quantity) return $this->reply("⚠️ المتاح في المخزن فقط {$product->stock} قطعة.");

        $context             = $session->context ?? [];
        $context['quantity'] = $quantity;

        $this->sessionService->updateStep($session, CustomerSession::STEP_AWAITING_GOVERNORATE, context: $context, productId: $session->product_id);

        return $this->reply("📍 اكتب اسم المحافظة:");
    }

    private function handleGovernorate(CustomerSession $session, string $message): array
    {
        if (mb_strlen($message) < 2) return $this->reply("⚠️ من فضلك اكتب اسم المحافظة.");

        $context                = $session->context ?? [];
        $context['governorate'] = $message;

        $this->sessionService->updateStep($session, CustomerSession::STEP_AWAITING_CITY, context: $context, productId: $session->product_id);

        return $this->reply("🏙 اكتب اسم المدينة أو المنطقة:");
    }

    private function handleCity(CustomerSession $session, string $message): array
    {
        if (mb_strlen($message) < 2) return $this->reply("⚠️ من فضلك اكتب اسم المدينة.");

        $context         = $session->context ?? [];
        $context['city'] = $message;

        $this->sessionService->updateStep($session, CustomerSession::STEP_AWAITING_STREET, context: $context, productId: $session->product_id);

        return $this->reply("🏠 اكتب العنوان بالتفصيل (الشارع + رقم البيت):");
    }

    private function handleStreet(CustomerSession $session, string $message): array
    {
        if (mb_strlen($message) < 5) return $this->reply("⚠️ من فضلك اكتب العنوان بشكل أوضح.");

        $context           = $session->context ?? [];
        $context['street'] = $message;

        $this->sessionService->updateStep($session, CustomerSession::STEP_AWAITING_NOTES, context: $context, productId: $session->product_id);

        return $this->reply(
            "📝 في أي ملاحظات إضافية للطلب؟\n" .
            "(اكتب الملاحظة أو اكتب *لا* للتخطي)"
        );
    }

    private function handleNotes(CustomerSession $session, string $message): array
    {
        $context          = $session->context ?? [];
        $context['notes'] = in_array(mb_strtolower($message), ['لا', 'no', '-', 'لأ']) ? null : $message;

        $product  = Product::find($session->product_id);
        $quantity = (int) ($context['quantity'] ?? 1);
        $total    = $product->price * $quantity;

        $this->sessionService->updateStep($session, CustomerSession::STEP_AWAITING_CONFIRM, context: $context, productId: $session->product_id);

        $paymentLabel = PaymentMethod::from($context['payment_method'])->label();

        return $this->reply(
            "📋 تأكيد الطلب:\n\n" .
            "👤 الاسم: {$context['name']}\n" .
            "📦 المنتج: {$product->name}\n" .
            "🔢 الكمية: {$quantity}\n" .
            "💰 الإجمالي: {$total} EGP\n" .
            "📍 المحافظة: {$context['governorate']}\n" .
            "🏙 المدينة: {$context['city']}\n" .
            "🏠 العنوان: {$context['street']}\n" .
            ($context['notes'] ? "📝 ملاحظات: {$context['notes']}\n" : '') .
            "💳 الدفع: {$paymentLabel}\n\n" .
            "1️⃣ تأكيد ✅\n" .
            "2️⃣ إلغاء ❌"
        );
    }

    private function handleConfirm(CustomerSession $session, string $message): array
    {
        if ($message === '2') return $this->handleReset($session);
        if ($message !== '1') return $this->reply("⚠️ رد بـ 1 للتأكيد أو 2 للإلغاء.");
        if (! $session->product_id) return $this->handleMainMenu($session);

        $context = $session->context ?? [];

        return DB::transaction(function () use ($session, $context) {

            $recentOrder = Order::where('customer_phone', $session->phone)
                ->where('product_id', $session->product_id)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->first();

            if ($recentOrder) return $this->reply("⏳ تم استلام طلبك بالفعل.");

            $order = $this->orderService->create([
                'product_id'      => $session->product_id,
                'customer_phone'  => $session->phone,
                'customer_name'   => $context['name']         ?? null,
                'payment_method'  => $context['payment_method'],
                'quantity'        => $context['quantity']      ?? 1,
                'governorate'     => $context['governorate']   ?? null,
                'city'            => $context['city']          ?? null,
                'street'          => $context['street']        ?? null,
                'notes'           => $context['notes']         ?? null,
            ]);

            $this->sessionService->reset($session);

            if ($context['payment_method'] === PaymentMethod::VISA->value) {
                $paymentUrl = $this->paymobService->createPaymentUrl($order);

                return [
                    'reply'       => "🎉 تم إنشاء الطلب رقم #{$order->id}\n" .
                                     "💰 المبلغ: {$order->amount} EGP\n\n" .
                                     "ادفع بالفيزا من خلال اللينك التالي 👇\n" .
                                     $paymentUrl,
                    'payment_url' => $paymentUrl,
                ];
            }

            return $this->reply(
                "🎉 تم إنشاء الطلب رقم #{$order->id}\n" .
                "💰 المبلغ: {$order->amount} EGP\n\n" .
                "✅ سيتم التواصل معك قريباً لتأكيد التوصيل 🚚"
            );
        });
    }

    /* ================= UTILITIES ================= */

    private function handleReset(CustomerSession $session): array
    {
        $this->sessionService->reset($session);
        return $this->handleMainMenu($session);
    }

    private function isResetCommand(string $message): bool
    {
        return in_array(strtolower($message), ['cancel', 'reset', 'menu', 'start', '0', '/start', 'إلغاء', 'رجوع'], true);
    }

    private function parsePaymentMethod(string $input): ?PaymentMethod
    {
        return match (trim($input)) {
            '1'     => PaymentMethod::CASH,
            '2'     => PaymentMethod::VISA,
            default => null,
        };
    }

    private function reply(string $text): array
    {
        return ['reply' => $text];
    }
}
