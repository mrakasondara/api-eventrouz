<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'image_thumb_url' => $this->image_thumb_url,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'location' => $this->location,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
            'ticket_categories' => TicketCategoryResource::collection($this->whenLoaded('ticketCategories'))
        ];
    }
}
