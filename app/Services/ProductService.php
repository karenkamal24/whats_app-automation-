<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * Common filler / stop words to ignore when parsing WhatsApp messages.
     */
    private const STOP_WORDS = [
        // Arabic
        'هل', 'في', 'من', 'على', 'عن', 'إلى', 'مع', 'هذا', 'هذه', 'أنا', 'انت',
        'عندك', 'عندكم', 'فيه', 'عايز', 'عاوز', 'عايزه', 'عاوزه', 'ممكن', 'لو',
        'يا', 'دي', 'ده', 'كام', 'بكام', 'سعر', 'منتج', 'متوفر', 'متاح', 'موجود',
        'اريد', 'أريد', 'ابي', 'ابغى', 'عطني', 'ابغا', 'طلب', 'اطلب',
        // English
        'i', 'a', 'the', 'is', 'do', 'you', 'have', 'any', 'want', 'need',
        'hi', 'hello', 'hey', 'please', 'can', 'get', 'me', 'show',
        'price', 'available', 'stock', 'product', 'order', 'buy',
    ];

    /**
     * Search active, in-stock products by a natural-language message.
     *
     * Splits the message into meaningful keywords and matches any of them
     * against product name or description.
     */
    public function search(string $term): Collection
    {
        $keywords = $this->extractKeywords($term);

        // If no meaningful keywords remain, try the full term as-is
        if (empty($keywords)) {
            $keywords = [trim($term)];
        }

        return Product::query()
            ->active()
            ->inStock()
            ->searchWords($keywords)
            ->with(['images:id,product_id,path'])
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    /**
     * Find a single product by ID (active & in stock).
     */
    public function findAvailable(int $productId): ?Product
    {
        return Product::query()
            ->active()
            ->inStock()
            ->with(['images:id,product_id,path'])
            ->find($productId);
    }

    /**
     * Extract meaningful keywords from a natural-language message.
     *
     * - Splits on whitespace and common punctuation
     * - Removes stop words (Arabic + English)
     * - Removes very short tokens (< 2 chars)
     *
     * @return string[]
     */
    private function extractKeywords(string $message): array
    {
        // Normalize: lowercase, strip punctuation except hyphens
        $message = mb_strtolower(trim($message));
        $message = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $message);

        // Split into words
        $words = preg_split('/\s+/', $message, -1, PREG_SPLIT_NO_EMPTY);

        // Filter out stop words and short tokens
        $stopWords = array_map('mb_strtolower', self::STOP_WORDS);

        return array_values(array_filter($words, function (string $word) use ($stopWords) {
            return mb_strlen($word) >= 2 && ! in_array($word, $stopWords, true);
        }));
    }
}
