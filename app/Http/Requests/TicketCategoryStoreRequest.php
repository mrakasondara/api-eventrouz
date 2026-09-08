<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TicketCategoryStoreRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        $this->merge([
            'is_package' => filter_var($this->is_package, FILTER_VALIDATE_BOOLEAN)
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|integer',
            'is_package' => 'required|boolean',
            'event_ticket_date' => 'nullable|array',
            'event_ticket_date*' => 'string',
            'quota' => 'required|integer|min:1',
        ];
    }
}
