<?php

namespace App\Models;

use App\Services\AppwriteStorageService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title','description','image_thumb','start_at','end_at','location','status'])]
class Event extends Model
{
    protected $appends = ['image_thumb_url'];

    public function ticketCategories(){
        return $this->hasMany(TicketCategory::class, 'event_id', 'id');
    }

    public function getImageThumbUrlAttribute(): ?string{
        if(!$this->image_thumb){
            return null;
        }

        $appwrite = app(AppwriteStorageService::class);
        return $appwrite->getFileViewUrl($this->image_thumb);
    }
}
