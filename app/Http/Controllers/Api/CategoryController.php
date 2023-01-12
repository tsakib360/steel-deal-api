<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function addCategorySeller(Request $request)
    {
        $validate=Validator::make($request->all(),[
            'name'=>'required',
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }
        $user=User::whereId(auth()->id())->first();
        if(is_null($user)) {
            return  $this->ErrorResponse(400,'No user found ..!');
        }
        if($user->role != 3) {
            return  $this->ErrorResponse(400,'You are not a seller ..!');
        }

        $category=Category::create([
            'name' =>$request->name,
            'user_id' =>auth()->id(),
            'description'=>$request->description,
        ]);


        if(!$category){
            return $this->ErrorResponse(400,'Somethings went wrong ..!');
        }

//        if($request->hasFile('product_image')){
//
//            foreach ($request->file('product_image') as $image){
//                $instock->addMedia($image)->toMediaCollection('product');
//            }
//
//        }
        return $this->SuccessResponse(200,'Category added successfully ..!');
    }

    public function getCategory(Request $request){
        if(!is_null($request->get('limit'))) {
            $data= tap(Category::where('user_id',auth()->id())->where('status',true)->paginate($request->limit)->appends('limit', $request->limit))->transform(function($listing){
                unset($listing['image']);
                unset($listing['status']);
                unset($listing['user_id']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                return $listing;
            });
        }else{
            $data= Category::where('user_id',auth()->id())->where('status',true)->get()->map(function($listing){
                unset($listing['image']);
                unset($listing['status']);
                unset($listing['user_id']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                return $listing;
            });
        }

        return $this->response($data);
    }

    public function addSubCategorySeller(Request $request)
    {
        $validate=Validator::make($request->all(),[
            'name'=>'required',
            'category_id'=>'required',
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }
        if(!Category::where('id',$request->category_id)->exists()){
            return  $this->ErrorResponse(400,'No category found ..!');
        }
        $user=User::whereId(auth()->id())->first();
        if(is_null($user)) {
            return  $this->ErrorResponse(400,'No user found ..!');
        }
        if($user->role != 3) {
            return  $this->ErrorResponse(400,'You are not a seller ..!');
        }

        $category=SubCategory::create([
            'name' =>$request->name,
            'user_id' =>auth()->id(),
            'category_id' =>$request->category_id,
            'description'=>$request->description,
        ]);


        if(!$category){
            return $this->ErrorResponse(400,'Somethings went wrong ..!');
        }

//        if($request->hasFile('product_image')){
//
//            foreach ($request->file('product_image') as $image){
//                $instock->addMedia($image)->toMediaCollection('product');
//            }
//
//        }
        return $this->SuccessResponse(200,'Subcategory added successfully ..!');
    }

    public function getSubCategory(Request $request){
        if(!is_null($request->get('limit'))) {
            $data= tap(SubCategory::where('user_id',auth()->id())->where('status',true)->with('category')->paginate($request->limit)->appends('limit', $request->limit))->transform(function($listing){
                unset($listing['image']);
                unset($listing['status']);
                unset($listing['user_id']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                unset($listing['category_id']);
                return $listing;
            });
        }else{
            $data= SubCategory::where('user_id',auth()->id())->where('status',true)->with('category')->get()->map(function($listing){
                unset($listing['image']);
                unset($listing['status']);
                unset($listing['user_id']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                unset($listing['category_id']);
                return $listing;
            });
        }

        return $this->response($data);
    }
}
