<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id','total_price','status'])]
class Order extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function ordersDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
}
