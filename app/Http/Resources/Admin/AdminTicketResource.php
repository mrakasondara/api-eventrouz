<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminTicketResource extends JsonResource
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
            'status_event' => $this->status_event,
            'ticket_code' => $this->ticket_code,
            'quantity' => $this->quantity,
            'ticket_date' => $this->event_ticket_date,
            'ticket_category' => $this->ticketCategory?->name,
            'event_id' => $this->ticketCategory?->event?->id,
            'event_location' => $this->ticketCategory?->event?->location,
            'event_image_thumb_url' => $this->ticketCategory?->event?->image_thumb_url,
            'user' => new UserResource($this->order?->user)
        ];
    }
}
