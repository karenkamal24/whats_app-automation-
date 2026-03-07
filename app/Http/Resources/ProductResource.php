<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => (float) $this->price,
            'stock'       => $this->stock,
            'is_active'   => $this->is_active,
            'images'      => ProductImageResource::collection($this->whenLoaded('images')),
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}



