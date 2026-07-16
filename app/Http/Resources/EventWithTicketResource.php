<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventWithTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'image_thumb' => $this->image_thumb,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'location' => $this->location,
            'status' => $this->status,
            'ticket_categories' => TicketWithEventResource::collection($this->whenLoaded('ticketCategories'))
        ];
    }
}
