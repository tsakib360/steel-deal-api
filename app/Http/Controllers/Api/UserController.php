<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function all_customers(Request $request)
    {
        if(!is_null($request->get('limit'))) {
            $products= tap(User::latest()->where('role', 4)->paginate($request->limit)->appends('limit', $request->limit))->transform(function($product){
                return $product;
            });
        }else{
            $products= User::latest()->where('role', 4)->get()->map(function($product){
                return $product;
            });
        }

        return $this->response($products);
    }
}
