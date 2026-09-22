<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id'])]
class Cart extends Model
{
    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function cartItems(){
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }
}
