<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
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
            'image'=>'required',
            'image.*'=>'image|mimes:jpg,jpeg,png,bmp|max:3000'
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

        if($request->hasFile('image')){
            $category->addMedia($request->image)->toMediaCollection('category_image');

        }
        return $this->SuccessResponse(200,'Category added successfully ..!');
    }

    public function getCategorySeller(Request $request){
        if(!is_null($request->get('limit'))) {
            $data= tap(Category::where('user_id',auth()->id())->where('parent_id', null)->where('status',true)->paginate($request->limit)->appends('limit', $request->limit))->transform(function($listing){
                $listing['thumb'] = $listing->getFirstMediaUrl('category_image');
                unset($listing['image']);
                unset($listing['status']);
                unset($listing['user_id']);
                unset($listing['parent_id']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                unset($listing['media']);
                return $listing;
            });
        }else{
            $data= Category::where('user_id',auth()->id())->where('parent_id', null)->where('status',true)->get()->map(function($listing){
                $listing['thumb'] = $listing->getFirstMediaUrl('category_image');
                unset($listing['image']);
                unset($listing['status']);
                unset($listing['user_id']);
                unset($listing['parent_id']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                unset($listing['media']);
                return $listing;
            });
        }

        return $this->response($data);
    }

    public function getCategory(Request $request){
        if(!is_null($request->get('limit'))) {
            $data= tap(Category::where('status',true)->paginate($request->limit)->appends('limit', $request->limit))->transform(function($listing){
                $listing['subcategory'] = $listing->subcategories;
                unset($listing['image']);
                unset($listing['status']);
                unset($listing['user_id']);
                unset($listing['parent_id']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                unset($listing['subcategories']);
                return $listing;
            });
        }else{
            $data= Category::where('status',true)->get()->map(function($listing){
                $listing['subcategory'] = $listing->subcategories;
                unset($listing['image']);
                unset($listing['status']);
                unset($listing['user_id']);
                unset($listing['parent_id']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                unset($listing['subcategories']);
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
            'image'=>'required',
            'image.*'=>'image|mimes:jpg,jpeg,png,bmp|max:3000'
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

        $category=Category::create([
            'name' =>$request->name,
            'user_id' =>auth()->id(),
            'parent_id' =>$request->category_id,
            'description'=>$request->description,
        ]);


        if(!$category){
            return $this->ErrorResponse(400,'Somethings went wrong ..!');
        }

        if($request->hasFile('image')){
            $category->addMedia($request->image)->toMediaCollection('category_image');
        }
        return $this->SuccessResponse(200,'Subcategory added successfully ..!');
    }

    public function getSubCategory(Request $request){
        if(!is_null($request->get('limit'))) {
            $data= tap(Category::where('user_id',auth()->id())->where('parent_id', '!=', null)->where('status',true)->with('category')->paginate($request->limit)->appends('limit', $request->limit))->transform(function($listing){
                $listing['thumb'] = $listing->getFirstMediaUrl('category_image');
                unset($listing['image']);
                unset($listing['status']);
                unset($listing['user_id']);
                unset($listing['parent_id']);
                unset($listing['category']['parent_id']);
                unset($listing['category']['created_at']);
                unset($listing['category']['updated_at']);
                unset($listing['category']['user_id']);
                unset($listing['category']['description']);
                unset($listing['category']['image']);
                unset($listing['category']['status']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                unset($listing['category_id']);
                unset($listing['media']);
                return $listing;
            });
        }else{
            $data= Category::where('user_id',auth()->id())->where('parent_id', '!=', null)->where('status',true)->with('category')->get()->map(function($listing){
                $listing['thumb'] = $listing->getFirstMediaUrl('category_image');
                unset($listing['image']);
                unset($listing['status']);
                unset($listing['user_id']);
                unset($listing['parent_id']);
                unset($listing['category']['parent_id']);
                unset($listing['category']['created_at']);
                unset($listing['category']['updated_at']);
                unset($listing['category']['user_id']);
                unset($listing['category']['description']);
                unset($listing['category']['image']);
                unset($listing['category']['status']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                unset($listing['category_id']);
                unset($listing['media']);
                return $listing;
            });
        }

        return $this->response($data);
    }

    public function getCatOrSubcatProducts(Request $request, $category_id)
    {
        $query = Product::query();
        if(!is_null($request->get('limit'))) {
            $products= tap($query->latest()->with('shop', 'size', 'instock')->where('category_id', $category_id)->paginate($request->limit)->appends('limit', $request->limit))->transform(function($product){
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
            $products= $query->latest()->with('shop', 'size', 'instock')->where('category_id', $category_id)->get()->map(function($product){
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
        }

        return $this->response($products);
    }

    public function getCatOrSubcatTYpeProducts(Request $request, $category_id, $type)
    {
        $query = Product::query();
        if($type == 'isi') {
            $query = $query->latest()->with('shop', 'size', 'instock')->whereHas('instock', function ($q) {
                $q->where('brand_type', 'isi');
            });
        }else{
            $query = $query->latest()->with('shop', 'size', 'instock')->whereHas('instock', function ($q) {
                $q->where('brand_type', '!=', 'isi');
            });
        }
        if(!is_null($request->get('limit'))) {
            $products= tap($query->where('category_id', $category_id)->paginate($request->limit)->appends('limit', $request->limit))->transform(function($product){
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
            $products= $query->where('category_id', $category_id)->get()->map(function($product){
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
        }

        return $this->response($products);
    }
}
