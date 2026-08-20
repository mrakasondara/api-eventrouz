<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
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
            'ticket_code' => $this->ticket_code,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'ticket_category_name' => $this->ticketCategory?->name,
            'event_title' => $this->ticketCategory?->event?->title
        ];
    }
}
