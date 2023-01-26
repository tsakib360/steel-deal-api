<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    //

    public function shop(Request $request){
        $validate= Validator::make($request->all(),[
            'shop_name'=>'required',
            'description'=>'required',
            'shop_number'=>'required',
            'mobile'=>'required',
            'address'=>'required',
            'open_time'=>'required',
            'close_time'=>'required',
            'latitude'=>'required',
            'longitude'=>'required',
            'banner'=>'required',
            'banner.*'=>'mimes:jpg,jpeg,png,bmp|max:3072',
            'shop_image'=>'required|mimes:jpg,jpeg,png,bmp|max:2000'
        ]);
        if($validate->fails()){
            return $this->ErrorResponse(400,$validate->errors());
        }
        if(Shop::where('user_id',auth()->id())->exists()){
            return  $this->ErrorResponse(400,'Your shop is already registered ..!');
        }


        $shop= Shop::create([
            'user_id'=>auth()->id(),
           'shop_name'=>$request->shop_name,
           'description'=>$request->description,
           'shop_number'=>$request->shop_number,
           'mobile'=>$request->mobile,
           'address'=>$request->address,
           'open_time'=>$request->open_time,
           'close_time'=>$request->close_time,
           'latitude'=>$request->latitude,
           'longitude'=>$request->longitude,
        ]);

        if(!$shop){
            return $this->ErrorResponse(400,'Somethings went wrong while add product ..!');
        }
        if($request->hasFile('banner')){
            foreach ($request['banner'] as $banner){
                $shop->addMedia($banner)->toMediaCollection('shop_banner');
            }
        }
        if($request->hasFile('shop_image')){
            $shop->addMedia($request->shop_image)->toMediaCollection('shop_image');
        }
        if($request->hasFile('shop_video')){
            $shop->addMedia($request->shop_video)->toMediaCollection('shop_video');
        }

        return $this->SuccessResponse(200,'Shop added successfully ..!');
    }

    public function get_self_shop(){
        $shop= Shop::where('user_id',auth()->id())->first();
        $banners= collect();
        foreach ($shop->getMedia('shop_banner') as $media){
           $banners->push($media->getFullUrl());
        }
        $shop['banner']= $banners;
        $shop['shop_image']= $shop->getFirstMediaUrl('shop_image');
        $shop['video']= $shop->getFirstMediaUrl('shop_video');
        unset($shop['media']);
        return $this->SuccessResponse(200,'Shop added successfully ..!',$shop);
    }

    public function getShopTime()
    {
        $shop= Shop::where('user_id',auth()->id())->first();
        if(is_null($shop)) {
            return $this->ErrorResponse(400,'No shop found ..!');
        }
        $data['open_time'] = Carbon::parse($shop->open_time)->format('g:i A');
        $data['close_time'] = Carbon::parse($shop->close_time)->format('g:i A');
        return $this->SuccessResponse(200,'Time fetch successfully..!',$data);
    }

    public function updateShopTime(Request $request)
    {
        $shop= Shop::where('user_id',auth()->id())->first();
        if(is_null($shop)) {
            return $this->ErrorResponse(400,'No shop found ..!');
        }
        $shop->open_time = !is_null($request->open_time) ? $request->open_time : 1;
        $shop->close_time = !is_null($request->close_time) ? $request->close_time : 1;
        $shop->save();
        return $this->SuccessResponse(200,'Successfully updated ..!');
    }

    public function updateShopStatus(Request $request)
    {
        $shop= Shop::where('user_id',auth()->id())->first();
        if(is_null($shop)) {
            return $this->ErrorResponse(400,'No shop found ..!');
        }
        $shop->is_online = $request->status;
        $shop->save();
        return $this->SuccessResponse(200,'Successfully updated ..!');
    }

    public function getAllShopsWithType($status)
    {
        $shops = Shop::where('is_online', $status)->get();
        return $this->SuccessResponse(200,'Successfully updated ..!', $shops);
    }

    public function searchShopByLocation(Request $request)
    {
        $validate= Validator::make($request->all(),[
            'latitude'=>'required',
            'longitude'=>'required',
            'distance' => 'required'
        ]);
        if($validate->fails()){
            return $this->ErrorResponse(400,$validate->errors());
        }
        $shops = Shop::distance($request->latitude, $request->longitude, $request->distance)->get();
        return $this->SuccessResponse(200,'Successfully updated ..!', $shops);
    }
}
