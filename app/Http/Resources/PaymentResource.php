<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order_id' => $this->order_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'gross_amount' => $this->gross_amount,
            'midtrans_transaction_id' => $this->midtrans_transaction_id,
            'payment_type' => $this->payment_type,
            'bank' => $this->bank,
            'va_number' => $this->va_number,
            'payment_status' => $this->payment_status,
            'fraud_status' => $this->fraud_status,
            'paid_at' => $this->paid_at,
        ];
    }
}
