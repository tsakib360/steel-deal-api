<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instock;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    public function add_product(Request $request){
        $validate= Validator::make($request->all(),[
           'name'=>'required',
           'size' =>'required',
           'price' =>'required',
            'random' => 'required',
            'clear_cut'=> 'required'
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }
        if(!Shop::where('user_id',auth()->id())->exists()){
            return  $this->ErrorResponse(400,'You don\'t have shop. Kindly create first to add product ..!');
        }
        $shop= Shop::where('user_id',auth()->id())->first();
        $product= Product::create([
            'shop_id'=> $shop->id,
           'user_id' =>auth()->id(),
           'name'=>$request->name,
           'size_id'=>$request->size,
           'category_id'=>!is_null($request->category_id) ? $request->category_id : 1,
           'price'=>$request->price,
           'random'=>$request->random,
           'clear_cut'=>$request->clear_cut
        ]);
        if(!$product){
            return $this->ErrorResponse(400,'Somethings went wrong while add product ..!');
        }

        return $this->SuccessResponse(200,'Product added successfully ..!');
    }

    public function product_names(Request $request){
        if(!is_null($request->get('limit'))) {
            $products= tap(Product::latest()->with('shop', 'size', 'instock')->paginate($request->limit)->appends('limit', $request->limit))->transform(function($product){
                $product['size']= $product->size;
                if(!is_null($product->instock)) {
                    $images=collect();
                    foreach ($product->instock->getMedia('product') as $img){
                        $images->push($img->getFullUrl());
                    }
                    $product['instock']['images']= $images;
                    unset($product['instock']['media']);
                }

                unset($product['size_id']);
                return $product;
            });
        }else{
            $products= Product::latest()->with('shop', 'size', 'instock')->get()->map(function($product){
                $product['size']= $product->size;
                $product['image']= $product->instock;
                unset($product['size_id']);
                return $product;
            });
        }

        return $this->response($products);
    }

    public function productListSeller(Request $request){
        if(!is_null($request->get('limit'))) {
            $products= tap(Product::where('user_id', auth()->id())->latest()->with('shop', 'size', 'instock')->paginate($request->limit)->appends('limit', $request->limit))->transform(function($product){
                $product['size']= $product->size;
                if(!is_null($product->instock)) {
                    $images=collect();
                    foreach ($product->instock->getMedia('product') as $img){
                        $images->push($img->getFullUrl());
                    }
                    $product['instock']['images']= $images;
                    unset($product['instock']['media']);
                }

                unset($product['size_id']);
                return $product;
            });
        }else{
            $products= Product::where('user_id', auth()->id())->latest()->with('shop', 'size', 'instock')->get()->map(function($product){
                $product['size']= $product->size;
                $product['image']= $product->instock;
                unset($product['size_id']);
                return $product;
            });
        }

        return $this->response($products);
    }

}

