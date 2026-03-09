<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductService
{
    private const STOP_WORDS = [
        'هل','في','من','على','عن','إلى','مع','هذا','هذه','أنا','انت',
        'عندك','عندكم','فيه','عايز','عاوز','عايزه','عاوزه','ممكن','لو',
        'يا','دي','ده','كام','بكام','سعر','منتج','متوفر','متاح','موجود',
        'اريد','أريد','ابي','ابغى','عطني','ابغا','طلب','اطلب','اسال','اسل',
        'بوصة','شاشة','شاشه','inch','inches',

        'i','a','the','is','do','you','have','any','want','need',
        'hi','hello','hey','please','can','get','me','show',
        'price','available','stock','product','order','buy',
    ];

    public function search(string $term): Collection
    {
        $keywords = $this->extractKeywords($term);

        if (empty($keywords)) {
            $keywords = [trim($term)];
        }

        $genericWords     = $this->getGenericWordsFromDB();
        $specificKeywords = $this->extractSpecificKeywords($keywords, $genericWords);

        Log::info('ProductService Search Debug', [
            'term'             => $term,
            'keywords'         => $keywords,
            'genericWords'     => $genericWords,
            'specificKeywords' => $specificKeywords,
        ]);

        $searchKeywords = !empty($specificKeywords)
            ? $specificKeywords
            : $keywords;

        $strictResults = Product::query()
            ->active()
            ->inStock()
            ->searchWordsStrict($searchKeywords)
            ->with(['images:id,product_id,path'])
            ->orderBy('name')
            ->limit(10)
            ->get();

        if ($strictResults->isNotEmpty()) {

            Log::info('Strict results', [
                'count'    => $strictResults->count(),
                'products' => $strictResults->pluck('name')->toArray(),
            ]);

            return $strictResults;
        }

        $fallback = Product::query()
            ->active()
            ->inStock()
            ->searchWords($keywords)
            ->with(['images:id,product_id,path'])
            ->orderBy('name')
            ->limit(10)
            ->get();

        Log::info('Fallback results', [
            'count'    => $fallback->count(),
            'products' => $fallback->pluck('name')->toArray(),
        ]);

        return $fallback;
    }

    public function findAvailable(int $productId): ?Product
    {
        return Product::query()
            ->active()
            ->inStock()
            ->with(['images:id,product_id,path'])
            ->find($productId);
    }

    private function getGenericWordsFromDB(): array
    {
        return Cache::remember('generic_product_words', 3600, function () {

            $totalProducts = Product::count();

            if ($totalProducts < 2) {
                return [];
            }

            $names = Product::pluck('name')
                ->merge(Product::pluck('name_ar')->filter())
                ->toArray();

            $wordCount = [];

            foreach ($names as $name) {

                $name  = mb_strtolower(trim($name));
                $words = preg_split('/[\s\-_]+/', $name, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($words as $word) {

                    $word = preg_replace('/[^\p{L}\p{N}]/u', '', $word);

                    if (mb_strlen($word) < 2) {
                        continue;
                    }

                    $wordCount[$word] = ($wordCount[$word] ?? 0) + 1;
                }
            }

            $threshold = max(2, (int) ceil($totalProducts * 0.4));

            $generic = array_keys(
                array_filter($wordCount, fn($count) => $count >= $threshold)
            );

            Log::info('Generic words computed', [
                'threshold' => $threshold,
                'total'     => $totalProducts,
                'generic'   => $generic,
                'wordCount' => $wordCount,
            ]);

            return $generic;
        });
    }

    private function extractSpecificKeywords(array $keywords, array $genericWords): array
    {
        $genericLower = array_map('mb_strtolower', $genericWords);

        $specific = array_filter($keywords, function (string $word) use ($genericLower) {

            $w = mb_strtolower($word);

            if (in_array($w, $genericLower, true)) {
                return false;
            }

            if (preg_match('/^\d+[a-z]*$/i', $word) || preg_match('/^[a-z]+\d+$/i', $word)) {
                return true;
            }

            if (preg_match('/^[a-z]+$/i', $word) && mb_strlen($word) >= 2) {
                return true;
            }

            if (preg_match('/^\p{Arabic}+$/u', $word) && mb_strlen($word) >= 3) {
                return true;
            }

            return false;
        });

        return array_values($specific);
    }

    private function extractKeywords(string $message): array
    {
        $message = mb_strtolower(trim($message));

        $message = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $message);

        $words = preg_split('/\s+/', $message, -1, PREG_SPLIT_NO_EMPTY);

        $stopWords = array_map('mb_strtolower', self::STOP_WORDS);

        return array_values(array_filter($words, function (string $word) use ($stopWords) {

            return mb_strlen($word) >= 2
                && !in_array($word, $stopWords, true);

        }));
    }
}
