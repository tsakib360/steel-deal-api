<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Instock extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $guarded =[];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function size(){
        return $this->belongsTo(Size::class,'size_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
    public function shop(){
        return $this->belongsTo(Shop::class,'shop_id');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->sharpen(10);
    }

}
