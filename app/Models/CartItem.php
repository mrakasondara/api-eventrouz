<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['cart_id', 'ticket_category_id', 'event_ticket_date', 'total_ticket'])]

class CartItem extends Model
{

    protected $cast = [
        'event_ticket_date' => 'array'
    ];

    public function cart(){
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    public function ticketCategory(){
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id', 'id');
    }
}
