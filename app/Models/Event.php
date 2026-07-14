<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title','description','image_thumb','start_at','end_at','location','status'])]
class Event extends Model
{
    //
}
