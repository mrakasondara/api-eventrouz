<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event_id','name','price','quota','reserved', 'event_ticket_date', 'is_package'])]
class TicketCategory extends Model
{
    protected $casts = [
        'event_ticket_date' => 'array'
    ];

    public function ordersDetails(){
        return $this->hasMany(OrderDetail::class);
    }

    public function event(){
        return $this->belongsTo(Event::class);
    }
}
