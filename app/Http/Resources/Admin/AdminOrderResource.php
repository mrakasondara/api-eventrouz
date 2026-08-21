<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\OrderDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'total_price' => $this->total_price,
            'status' => $this->status,
            'created_at' => $this->created_at->format('d M Y H:i'),
            'details' => OrderDetailResource::collection($this->whenLoaded('ordersDetails'))
        ];
    }
}
