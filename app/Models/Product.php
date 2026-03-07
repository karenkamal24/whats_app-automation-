<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
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

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

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
        return $this->belongsTo(\App\Models\Category::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Accessors                                                          */
    /* ------------------------------------------------------------------ */

    /**
     * Get main image URL (first image).
     */
    public function getImageUrlAttribute(): ?string
    {
        $image = $this->images()->first();

        return $image?->url;
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Search by single term.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Search by multiple words.
     */
    public function scopeSearchWords($query, array $words)
    {
        return $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->orWhere('name', 'LIKE', "%{$word}%")
                  ->orWhere('description', 'LIKE', "%{$word}%");
            }
        });
    }
}
