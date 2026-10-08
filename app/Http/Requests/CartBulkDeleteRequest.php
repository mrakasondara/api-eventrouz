<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartBulkDeleteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cart_item_ids' => 'required|array|min:1',
            'cart_item_ids.*' => 'required|integer|exists:cart_items,id',
        ];
    }
}
