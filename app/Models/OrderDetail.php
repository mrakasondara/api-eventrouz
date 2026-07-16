<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['order_id','quantity','ticket_code', 'ticket_category_id'])]
class OrderDetail extends Model
{
    protected $table = 'orders_details';
    
    public function order()
    {
        $this->belongsTo(Order::class);
    }

    public function ticketCategory()
    {
        $this->belongsTo(TicketCategory::class);
    }
}

