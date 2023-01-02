<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use App\Models\Verification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
/**
 * Seller Registration
 */

public function register(Request $request){
  $validate= Validator::make($request->all(),[
      'name'=>'required',
      'email'=>'required|unique:users,email',
      'address' =>'required',
      'country' =>'required',
      'state' =>'required',
      'city' =>'required',
      'pin_code'=>'required',
      'pan_card' =>'required',
      'gst_number'=>'required',
      'cin_number'=>'required',
      'aadhar_number'=>'required',
      'iec_number'=>'required',
      'phone' =>'required|unique:users,phone',
      'aadhar_img'=>'required|image|mimes:jpg,png,jpeg,gif,svg|max:3072',
      'pan_img'=>'required|image|mimes:jpg,png,jpeg,gif,svg|max:3072',
      'cin_img'=>'required|image|mimes:jpg,png,jpeg,gif,svg|max:3072',
      'gst_img'=>'required|image|mimes:jpg,png,jpeg,gif,svg|max:3072',
      'iec_img'=>'required|image|mimes:jpg,png,jpeg,gif,svg|max:3072',
       'type'=>'required|in:seller,buyer,transporter'
  ]);
    if($validate->fails()){
        return $this->ErrorResponse(400,$validate->messages());
    }

    $user= User::create([
       'name'=>$request->name,
       'email'=>$request->email,
       'phone'=>$request->phone,
        'address' =>$request->address,
        'country' =>$request->country,
        'state' =>$request->state,
        'city' =>$request->city,
        'pin_code'=>$request->pin_code,
        'pan_card' =>$request->pan_card,
        'gst_number'=>$request->gst_number,
        'cin_number'=>$request->cin_number,
        'aadhar_number'=>$request->aadhar_number,
        'role'=> $this->check_role($request->type),
        'iec_number'=>$request->iec_number,
    ]);
    if($user){
       if($request->hasFile('profile')){
           $user->addMedia($request->profile)->toMediaCollection('profile');
       }
        if($request->hasFile('pan_img')){
            $user->addMedia($request->pan_img)->toMediaCollection('pan');
        }
        if($request->hasFile('gst_img')){
            $user->addMedia($request->gst_img)->toMediaCollection('gst');
        }
        if($request->hasFile('aadhar_img')){
            $user->addMedia($request->aadhar_img)->toMediaCollection('aadhar');
        }
        if($request->hasFile('cin_img')){
            $user->addMedia($request->cin_img)->toMediaCollection('cin');
        }
        if($request->hasFile('iec_img')){
            $user->addMedia($request->iec_img)->toMediaCollection('iec');
        }
    }
    if(!$user){
        return $this->ErrorResponse(400,'Something went wrong..! ');
    }

    return  $this->SuccessResponse(201,'User register successfully ..!');
}
    public function login(Request $request)
    {
        $validate= Validator::make($request->all(),[
            'username'=>'required'
        ],[
            'username.required'=>'Please enter Email or Mobile ..'
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->errors()->first());
        }
        $user = User::where('email', $request->username)->orWhere('phone', $request->username)->exists();
        if (!$user) {
            return $this->ErrorResponse('400', 'invalid user..!');
        }
//        $otp = rand(99999, 999999);
        $otp= '123456';
        $verify = Verification::create([
            'username' => $request->username,
            'otp' => $otp,
            'is_expire' => false,
            'token' => str::random(60)
        ]);
        if (!$verify) {
            return $this->ErrorResponse(400, 'Something went wrong. While sending otp.! ');
        }
        return $this->SuccessResponse(200,'OTP sent  to your register Email/Phone ..!',$verify['token']);
    }

    public function verify_otp(Request $request){
      $validate= Validator::make($request->all(),[
          'token'=> 'required',
          'otp'=>'required',
      ]);

        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->errors());
        }
      $verify= Verification::where(['token'=>$request->token,'otp'=>$request->otp])->where('created_at', '>=', Carbon::now()->subMinutes(10)->toDateTimeString())->where('is_expire', '=', false)->first();
        if (!$verify) {
            return $this->ErrorResponse(400, 'Something went wrong.or OTP expire please try again');
        }
        $user= User::where('email',$verify->username)->orWhere('phone',$verify->username)->first();
        $verify->delete();
        $user['token']='Bearer ' . $user->createToken('auth_token')->plainTextToken;
        return $this->SuccessResponse(200,'Login successfully ..!',$user);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->SuccessResponse(200, 'You have successfully logged out', null);
    }

    public function authentication(){
        return $this->ErrorResponse(401,'authentication failed');
    }



    public function check_role($value){
        if($value=='seller'){
            return 3;
        }
        if($value=='buyer'){
            return 4;
        }
        if($value== 'transporter'){
            return 5;
        }
        return false;
    }

}
