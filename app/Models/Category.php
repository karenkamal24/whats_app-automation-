<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'name_ar'];


    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }



    public function getLocalizedNameAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? ($this->name_ar ?: $this->name)
            : ($this->name ?: $this->name_ar);
    }
}
