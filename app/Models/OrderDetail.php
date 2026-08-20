<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['order_id','quantity','price','ticket_code', 'ticket_category_id'])]
class OrderDetail extends Model
{
    protected $table = 'orders_details';
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function ticketCategory()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id','id');
    }
}

