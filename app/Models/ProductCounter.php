<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCounter extends Model
{
    use HasFactory;

    protected $casts = [
        'history' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function stock()
    {
        return $this->belongsTo(Instock::class, 'stock_id');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id');
    }
}
