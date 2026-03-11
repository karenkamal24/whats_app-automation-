<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'keywords',
        'price',
        'stock',
        'is_active',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'stock'     => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->images()->first()?->url;
    }

    public function getLocalizedNameAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? ($this->name_ar ?: $this->name)
            : ($this->name ?: $this->name_ar);
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'ar'
            ? ($this->description_ar ?: $this->description)
            : ($this->description ?: $this->description_ar);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('name_ar', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhere('description_ar', 'LIKE', "%{$term}%")
              ->orWhere('keywords', 'LIKE', "%{$term}%");
        });
    }

    public function scopeSearchWords($query, array $words)
    {
        return $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->orWhere('name', 'LIKE', "%{$word}%")
                  ->orWhere('name_ar', 'LIKE', "%{$word}%")
                  ->orWhere('description', 'LIKE', "%{$word}%")
                  ->orWhere('description_ar', 'LIKE', "%{$word}%")
                  ->orWhere('keywords', 'LIKE', "%{$word}%");
            }
        });
    }

 public function scopeSearchWordsStrict($query, array $words)
{
    return $query->where(function ($query) use ($words) {

        foreach ($words as $word) {

            $query->where(function ($q) use ($word) {

                if (is_numeric($word)) {

                    $pattern = '\\b' . preg_quote($word, '/') . '\\b';

                    $q->whereRaw("name REGEXP ?", [$pattern])
                      ->orWhereRaw("name_ar REGEXP ?", [$pattern])
                      ->orWhereRaw("keywords REGEXP ?", [$pattern]);

                } else {

                    $q->where('name', 'LIKE', "%{$word}%")
                      ->orWhere('name_ar', 'LIKE', "%{$word}%")
                      ->orWhere('keywords', 'LIKE', "%{$word}%");

                }

            });

        }

    });
}
}
