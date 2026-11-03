<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class checkInTicketResource extends JsonResource
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
            'quantity' => $this->quantity,
            'ticket_code' => $this->ticket_code,
            'ticket_category' => $this->ticketCategory?->name,
            'user_name' => $this->order->user?->name,
            'user_email'=> $this->order->user?->email,
            'event_statistic' => [
                'total_checkedin' => $this->total_checkedin,
                'remaining' => $this->remaining,
            ]
        ];
    }
}
