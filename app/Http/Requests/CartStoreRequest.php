<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CartStoreRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'event_ticket_date' => 'nullable|array',
            'event_ticket_date*' => 'string',
            'total_ticket' => 'required|integer|min:1'
        ];
    }
}
