<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instock;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstockController extends Controller
{

    public function add_stock(Request $request)
    {
        $validate=Validator::make($request->all(),[
            'product'=>'required',
            'basic_price'=>'required',
            'description'=>'required',
            'brand_type'=>'required',
            'size'=>'required',
            'length'=>'required',
            'loading_amount'=>'required',
            'quantity'=>'required',
            'product_image'=>'required',
            'product_image.*'=>'image|mimes:jpg,jpeg,png,bmp|max:3000'
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }
        if(!Shop::where('user_id',auth()->id())->exists()){
            return  $this->ErrorResponse(400,'You don\'t have shop. Kindly create first to add product ..!');
        }
        $shop= Shop::where('user_id',auth()->id())->first();

        $instock=Instock::create([
            'shop_id' =>$shop->id,
            'user_id' =>auth()->id(),
            'product_id'=>$request->product,
            'basic_price'=>$request->basic_price,
            'description'=>$request->description,
            'brand_type'=>$request->brand_type,
            'size_id'=>$request->size,
            'length'=>$request->length,
            'loading_amount'=>$request->loading_amount,
            'quantity'=>$request->quantity,
            'available' =>$request->quantity>0? true:false,
        ]);


        if(!$instock){
            return $this->ErrorResponse(400,'Somethings went wrong while add product ..!');
        }

        if($request->hasFile('product_image')){

            foreach ($request->file('product_image') as $image){
                $instock->addMedia($image)->toMediaCollection('product','thumb');
            }

        }
        return $this->SuccessResponse(200,'Product added successfully ..!');
    }

    public function get_stock(){
        $data= Instock::where('user_id',auth()->id())->where('status',true)->get()->map(function($listing){
            $images=collect();
            foreach ($listing->getMedia('product') as $img){
                 $images->push($img->getFullUrl());
            }
            $listing['user_name']= $listing->user->name;
            $listing['size_name']= $listing->size->size;
            $listing['product_name']= $listing->product->name;
            $listing['image']=$images;
            unset($listing['user']);
            unset($listing['user_id']);
            unset($listing['size']);
            unset($listing['size_id']);
            unset($listing['product_id']);
            unset($listing['product']);
            unset($listing['shop_id']);
            unset($listing['media']);
                return $listing;
        });

     return $this->response($data);
    }

    public function delete_stock($id){
        $stock= Instock::where(['user_id'=>auth()->id(),'id'=>$id])->first();
        $stock->clearMediaCollection('product');
        $stock->delete();
        return $this->SuccessResponse(200,'product deleted successfully ..!');
    }

    public function update_stock(Request $request){

        $instock= Instock::where(['user_id'=> auth()->id(),'id'=>$request->id])->first();
        $instock->update([
            'shop_id'=> $instock->shop_id,
            'user_id' =>$instock->user_id,
            'product_id'=>$request->product,
            'basic_price'=>$request->basic_price,
            'description'=>$request->description,
            'brand_type'=>$request->brand_type,
            'size_id'=>$request->size,
            'length'=>$request->length,
            'loading_amount'=>$request->loading_amount,
            'quantity'=>$request->quantity,
            'available' =>$request->quantity>0? true:false,
        ]);

        if($request->hasFile('product_image')){
            $instock->clearMediaCollection('product');
            foreach ($request->product_image as $image){
                $instock->addMedia($image)->toMediaCollection('product');
            }

        }
        return $this->SuccessResponse(200,'Product updated successfully ..!');

    }

    public function buyer_product_list($id){
        $products= Instock::where('product_id',$id)->get()->map(function ($listing){
            $listing['product_name']=$listing->product->name;
            $listing['shop']= $listing->shop;
            return $listing;
        });

        return $this->response($products);
    }

}
