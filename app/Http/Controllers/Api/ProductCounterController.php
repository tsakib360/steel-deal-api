<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instock;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCounter;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductCounterController extends Controller
{
    public function createProductCounter(Request $request)
    {
        $validate= Validator::make($request->all(),[
            'product_id'=>'required',
            'stock_id'=>'required',
            'size_id'=>'required',
            'counter_price'=>'required',
            'counter_qty'=>'required',
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }

        $product = Product::whereId($request->product_id)->first();
        if(is_null($product)) {
            return  $this->ErrorResponse(400,'No product found ..!');
        }
        if(!Instock::whereId($request->stock_id)->where('product_id', $request->product_id)->exists()) {
            return  $this->ErrorResponse(400,'No stock found for the product ..!');
        }
        if(!Size::whereId($request->size_id)->exists()) {
            return  $this->ErrorResponse(400,'No size found ..!');
        }

        $counter = new ProductCounter();
        $counter->product_id = $request->product_id;
        $counter->stock_id = $request->stock_id;
        $counter->size_id = $request->size_id;
        $counter->counter_price = $request->counter_price;
        $counter->counter_qty = $request->counter_qty;
        $counter->buyer_id = Auth::id();
        $counter->seller_id = $product->user_id;
        $counter->history = array(['buyer' => $request->counter_price]);
        $counter->save();
        return  $this->ErrorResponse(200,'A counter price is notify to seller ..!');

    }

    public function productCounterListByBuyer()
    {
        $data= ProductCounter::where('buyer_id', Auth::id())->where('is_accepted', 0)->with('product', 'stock', 'size')->get();
        return  $this->SuccessResponse(200,'Data fetch successfully ..!',$data);
    }

    public function productCounterListBySeller()
    {
        $data= ProductCounter::where('seller_id', Auth::id())->where('is_accepted', 0)->with('product', 'stock', 'size')->get();
        return  $this->SuccessResponse(200,'Data fetch successfully ..!',$data);
    }

    public function productCounterPriceChangeByBuyer(Request $request, $counter_id)
    {
        $validate= Validator::make($request->all(),[
            'counter_price'=>'required',
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }
        $counter = ProductCounter::whereId($counter_id)->where('is_accepted', 0)->first();
        if(is_null($counter)) {
            return  $this->ErrorResponse(400,'No counter found ..!');
        }
        $count_arr = [];
        foreach ($counter->history as $c) {
            array_push($count_arr, $c);
        }
        array_push($count_arr, (object)['buyer' => $request->counter_price]);
        $counter->counter_price = $request->counter_price;
        $counter->history = $count_arr;
        $counter->save();
        return  $this->SuccessResponse(200,'Counter price updated ..!',);
    }

    public function productCounterPriceChangeBySeller(Request $request, $counter_id)
    {
        $validate= Validator::make($request->all(),[
            'counter_price'=>'required',
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }
        $counter = ProductCounter::whereId($counter_id)->where('is_accepted', 0)->first();

        if(is_null($counter)) {
            return  $this->ErrorResponse(400,'No counter found ..!');
        }
        $count_arr = [];
        foreach ($counter->history as $c) {
            array_push($count_arr, $c);
        }
        array_push($count_arr, (object)['seller' => $request->counter_price]);
        $counter->counter_price = $request->counter_price;
        $counter->history = $count_arr;
        $counter->save();
        return  $this->SuccessResponse(200,'Counter price updated ..!',);
    }

    public function counterProductAccept($counter_id)
    {
        $counter = ProductCounter::whereId($counter_id)->where('is_accepted', 0)->first();

        if(is_null($counter)) {
            return  $this->ErrorResponse(400,'No counter found ..!');
        }

        $counter->is_accepted = 1;
        $counter->save();

        $cart = OrderItem::create([
            'user_id' => $counter->buyer_id,
            'product_id' => $counter->product_id,
            'stock_id' => $counter->stock_id,
            'size_id' => $counter->size_id,
            'qty' => $counter->counter_qty,
            'price' => $counter->counter_price,
            'total' => $counter->counter_qty * $counter->counter_price,
        ]);
        if(!$cart){
            return $this->ErrorResponse(400,'Somethings went wrong while add cart ..!');
        }
        return $this->SuccessResponse(200,'Counter successfully accepted. Product in shopping cart to buyer ..!');
    }
}
