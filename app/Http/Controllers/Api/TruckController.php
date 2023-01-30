<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TruckController extends Controller
{
    public function allTrucks()
    {
        $data = Truck::where('user_id', Auth::id())->get()->map(function($listing){
            $listing['license']=!empty($listing->getFirstMediaUrl('license')) ? $listing->getFirstMediaUrl('license') : null;
            unset($listing['media']);
            return $listing;
        });
        return  $this->SuccessResponse(200,'Data fetch successfully ..!',$data);
    }

    public function addTruck(Request $request)
    {
        $validate= Validator::make($request->all(),[
            'truck_no'=>'required',
            'rc'=>'required',
            'license'=>'required|image|mimes:jpg,png,jpeg,gif,svg|max:3072',
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }

        $truck = Truck::create([
            'user_id' => Auth::id(),
            'truck_no' => $request->truck_no,
            'rc' => $request->rc,
        ]);

        if($truck){
            if($request->hasFile('license')){
                $truck->addMedia($request->license)->toMediaCollection('license');
            }
        }

        if(!$truck){
            return $this->ErrorResponse(400,'Something went wrong..! ');
        }

        return  $this->SuccessResponse(201,'A truck added successfully ..!');
    }

    public function updateTruck(Request $request, $id)
    {
        $truck = Truck::whereId($id)->where('user_id', Auth::id())->first();
        if(is_null($truck)) {
            return $this->ErrorResponse(400,'No truck found! ');
        }

        $truck->update([
            'truck_no' => !is_null($request->truck_no) ? $request->truck_no : $truck->truck_no,
            'rc' => !is_null($request->rc) ? $request->rc : $truck->truck_no,
        ]);

        if($request->hasFile('license')){
            $truck->clearMediaCollection('license');
            $truck->addMedia($request->license)->toMediaCollection('license');

        }
        return $this->SuccessResponse(200,'Truck updated successfully ..!');
    }

    public function deleteTruck($id)
    {
        $truck = Truck::whereId($id)->where('user_id', Auth::id())->first();
        if(is_null($truck)) {
            return $this->ErrorResponse(400,'No truck found! ');
        }
        $truck->delete();
        $truck->clearMediaCollection('license');
        return $this->SuccessResponse(200,'Truck deleted successfully ..!');

    }
}
