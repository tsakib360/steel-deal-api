<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded =[];

    public function instock(){
        return $this->hasOne(Instock::class,'product_id');
    }

    public function size(){
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function shop(){
        return $this->belongsTo(Shop::class, 'shop_id');
    }

}
