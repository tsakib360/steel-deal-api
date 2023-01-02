<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function instock(){
        return $this->hasOne(Instock::class,'size_id');
    }

    public function product(){
        return $this->hasOne(Product::class,'size_id');
}

}
