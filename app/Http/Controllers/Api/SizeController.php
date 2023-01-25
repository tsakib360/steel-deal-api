<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomSizeRequest;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SizeController extends Controller
{
    public function size(){
        $sizes= Size::all();
        return  $this->SuccessResponse(200,'Data fetch successfully ..!',$sizes);
    }

    public function sizeRequestByUser(Request $request)
    {
        $validate= Validator::make($request->all(),[
            'product_id'=>'required',
            'size' =>'required',
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }

        $product = Product::whereId($request->product_id)->first();
        if(is_null($product)) {
            return  $this->ErrorResponse(400,'Product not found ..!');
        }

        $custom_size_request = new CustomSizeRequest();
        $custom_size_request->product_id = $request->product_id;
        $custom_size_request->user_id = Auth::id();
        $custom_size_request->size = $request->size;
        $custom_size_request->save();
        return  $this->SuccessResponse(200,'Size request successfully submitted ..!');
    }
}
