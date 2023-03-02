<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function getUserAddresses()
    {
        $data= Address::where('user_id', Auth::id())->get()->map(function($listing) {
            unset($listing['user_id']);
            unset($listing['created_at']);
            unset($listing['updated_at']);
            return $listing;
        });
        return  $this->SuccessResponse(200,'Data fetch successfully ..!',$data);
    }
    public function getUserDefaultAddresses()
    {
        $data= Address::where('user_id', Auth::id())->first();
        unset($data['user_id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        return  $this->SuccessResponse(200,'Data fetch successfully ..!',$data);
    }

    public function addUserAddress(Request $request)
    {
        DB::beginTransaction();
        try {
            $validate=Validator::make($request->all(),[
                'address'=>'required',
                'is_default'=>'required',
            ]);
            if($validate->fails()){
                return  $this->ErrorResponse(400,$validate->messages());
            }
            if($request->is_default == 1) {
                if(Address::where('user_id', Auth::id())->exists()) {
                    DB::rollBack();
                    return $this->ErrorResponse(400,'You have already a default address. So first make it updated!');
                }
            }

            $obj['address'] = $request->address;
            $address = new Address();
            $address->user_id = Auth::id();
            $address->address = $obj;
            $address->is_default = $request->is_default;
            $address->save();

            DB::commit();
            return $this->SuccessResponse(200,'Address added successfully ..!');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ErrorResponse(400,$e->getMessage());
        }

    }

    public function updateUserAddress(Request $request, $address_id)
    {
        DB::beginTransaction();
        try {
            if($request->is_default == 1) {
                if(Address::where('user_id', Auth::id())->exists()) {
                    DB::rollBack();
                    return $this->ErrorResponse(400,'You have already a default address. So first make it updated!');
                }
            }
            $address = Address::whereId($address_id)->first();
            if(is_null($address)) {
                DB::rollBack();
                return $this->ErrorResponse(400,'No address found!');
            }
            $obj['address'] = $request->address;
            $address->address = !empty($obj) ? $obj : $address->address;
            $address->is_default = !is_null($request->is_default) ? $request->is_default : $address->is_default;
            $address->save();

            DB::commit();
            return $this->SuccessResponse(200,'Address updated successfully ..!');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ErrorResponse(400,$e->getMessage());
        }
    }

    public function deleteUserAddress($address_id)
    {
        DB::beginTransaction();
        try {
            $address = Address::whereId($address_id)->first();
            if(is_null($address)) {
                DB::rollBack();
                return $this->ErrorResponse(400,'No address found!');
            }

            if(Order::where('address_id', $address_id)->exists()) {
                DB::rollBack();
                return $this->ErrorResponse(400,'This address is used in order! please change them first!');
            }

            $address->delete();

            DB::commit();
            return $this->SuccessResponse(200,'Address deleted successfully ..!');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ErrorResponse(400,$e->getMessage());
        }

    }
}
