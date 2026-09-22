<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'ticket_category_id' => $this->ticket_category_id,
            'event_ticket_date' => $this->event_ticket_date,
            'total_ticket' => $this->total_ticket,
            'ticket_category' => new CartItemTicketResource($this->whenLoaded('ticketCategory'))
        ];
    }
}
