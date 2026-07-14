<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EventStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'image_thumb' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'location' => 'required|string|max:255',
            'status' => 'required|string|in:draft,launch,end',
        ];
    }
}
