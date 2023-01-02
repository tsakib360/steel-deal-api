<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Shop extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;
    protected $guarded=[];

    public function instock(){
        return $this->hasOne(Instock::class,'shop_id');
    }
}
