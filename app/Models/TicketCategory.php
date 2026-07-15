<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event_id','name','price','quota','reserved'])]
class TicketCategory extends Model
{
    public function ordersDetails(){
        return $this->hasMany(OrderDetail::class);
    }

    public function event(){
        return $this->belongsTo(Event::class);
    }
}
