<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'phone'      => $this->phone,
            'step'       => $this->step,
            'product_id' => $this->product_id,
            'data'       => $this->data,
        ];
    }
}



