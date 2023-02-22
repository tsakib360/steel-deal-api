<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Instock;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    public function add_product(Request $request){
        $validate= Validator::make($request->all(),[
           'name'=>'required',
           'sizes' =>'required|array',
           'price' =>'required',
            'random' => 'required',
            'clear_cut'=> 'required',
            'category_id' => 'required'
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }
        if(!Shop::where('user_id',auth()->id())->exists()){
            return  $this->ErrorResponse(400,'You don\'t have shop. Kindly create first to add product ..!');
        }
        $shop= Shop::where('user_id',auth()->id())->first();
        $category= Category::whereId($request->category_id)->first();
        if(is_null($category)) {
            return  $this->ErrorResponse(400,'No category found ..!');
        }

        $size_arr = array();
        foreach($request->sizes as $size) {
           if(!Size::whereId($size)->exists()) {
               return  $this->ErrorResponse(400,'Unknown size ('.$size.') you given ..!');
           }
           array_push($size_arr, (int) $size);
        }

        $obj['name'] = $request->name;
        if(!empty($category->measurement_attributes)) {
            foreach($category->measurement_attributes as $attr){
                if(!$request->has($attr)) {
                    return $this->ErrorResponse(400, $attr.' is required!');
                }
                if(is_null($request->input($attr))) {
                    return $this->ErrorResponse(400, $attr.' is required!');
                }
                $obj[$attr] = $request->input($attr);
            }
        }
        unset($obj['name']);
        $product= Product::create([
            'shop_id'=> $shop->id,
           'user_id' =>auth()->id(),
           'name'=>$request->name,
           'sizes'=>$size_arr,
           'category_id'=>!is_null($request->category_id) ? $request->category_id : 1,
           'price'=>$request->price,
           'random'=>$request->random,
           'clear_cut'=>$request->clear_cut,
            'measurements' => !empty($obj) ? $obj : null,
        ]);
        if(!$product){
            return $this->ErrorResponse(400,'Somethings went wrong while add product ..!');
        }

        return $this->SuccessResponse(200,'Product added successfully ..!');
    }

    public function updateProduct(Request $request, $product_id)
    {
        DB::beginTransaction();
        try {
            $validate= Validator::make($request->all(),[
                'sizes' =>'array',
            ]);
            if($validate->fails()){
                DB::rollBack();
                return  $this->ErrorResponse(400,$validate->messages());
            }
            $product = Product::whereId($product_id)->first();
            if(is_null($product)){
                return $this->ErrorResponse(400,'No product found ..!');
            }
            $size_arr = $product->sizes;
            if($request->has('sizes')) {
                foreach ($request->sizes as $size) {
                    if(!in_array($size, $size_arr)) {
                        if(Size::whereId($size)->exists()) {
                            array_push($size_arr, (int) $size);
                        }

                    }
                }
            }
            $category= Category::whereId($product->category_id)->first();
            if(is_null($category)) {
                return  $this->ErrorResponse(400,'No category found ..!');
            }
            $obj['name'] = $request->name;
            if(!empty($category->measurement_attributes)) {
                foreach($category->measurement_attributes as $attr){
                    if(!$request->has($attr)) {
                        return $this->ErrorResponse(400, $attr.' is required!');
                    }
                    if(is_null($request->input($attr))) {
                        return $this->ErrorResponse(400, $attr.' is required!');
                    }
                    $obj[$attr] = $request->input($attr);
                }
            }
            unset($obj['name']);
            $product->update([
                'name'=>!is_null($request->name) ? $request->name : $product->name,
                'sizes'=>$size_arr,
                'category_id'=>!is_null($request->category_id) ? $request->category_id : $product->category_id,
                'price'=>!is_null($request->price) ? $request->price : $product->price,
                'random'=>!is_null($request->random) ? $request->random : $product->random,
                'clear_cut'=>!is_null($request->clear_cut) ? $request->clear_cut : $product->clear_cut,
                'measurements' => !empty($obj) ? $obj : $product->measurements,
            ]);
            $stock = Instock::where('product_id', $product->id)->first();
            if(!is_null($stock)){
                $stock->basic_price = !is_null($request->price) ? $request->price : $stock->basic_price;
                $stock->description = !is_null($request->description) ? $request->description : $stock->description;
                $stock->save();
                if($request->hasFile('product_image')){
                    $stock->clearMediaCollection('product');
                    foreach ($request->product_image as $image){
                        $stock->addMedia($image)->toMediaCollection('product');
                    }

                }
            }

            DB::commit();
            return $this->SuccessResponse(200,'Product updated successfully ..!');
        }catch (\Exception $e) {
            DB::rollBack();
            return $this->ErrorResponse(400,$e->getMessage());
        }
    }

    public function product_names(Request $request){
        if(!is_null($request->get('limit'))) {
            $products= tap(Product::latest()->with('instock')->has('instock')->paginate($request->limit)->appends('limit', $request->limit))->transform(function($product){
                $size_arr = array();
                if(count($product['sizes']) != 0) {
                    foreach ($product['sizes'] as $size) {
                        $sz = Size::whereId($size)->first();
                        array_push($size_arr, $sz);
                    }
                }
                $product['sizes'] = $size_arr;
                if(!is_null($product->instock)) {
                    $images=collect();
                    foreach ($product->instock->getMedia('product') as $img){
                        $images->push($img->getFullUrl());
                    }
                    $product['images']= $images;
                    $product['description']= $product['instock']['description'];
                    $product['basic_price']= $product['instock']['basic_price'];
                    unset($product['instock']['media']);
                    unset($product['instock']['shop_id']);
                    unset($product['instock']['user_id']);
                    unset($product['instock']['product_id']);
                    unset($product['instock']['size_id']);
                    unset($product['instock']['description']);
                    unset($product['instock']['created_at']);
                    unset($product['instock']['updated_at']);
                }
                $product['category'] = $product->category;
                $product['shop'] = $product->shop;

                unset($product['size_id']);
                unset($product['price']);
                unset($product['shop_id']);
                unset($product['user_id']);
                unset($product['category_id']);
                unset($product['created_at']);
                unset($product['updated_at']);
                unset($product['category']['created_at']);
                unset($product['category']['updated_at']);
                unset($product['shop']['user_id']);
                unset($product['shop']['created_at']);
                unset($product['shop']['updated_at']);
//                unset($product['size']['created_at']);
//                unset($product['size']['updated_at']);
                return $product;
            });
        }else{
            $products= Product::latest()->with('instock')->has('instock')->get()->map(function($product){
//                $product['size']= $product->size;
                $size_arr = array();
                if(count($product['sizes']) != 0) {
                    foreach ($product['sizes'] as $size) {
                        $sz = Size::whereId($size)->first();
                        array_push($size_arr, $sz);
                    }
                }
                $product['sizes'] = $size_arr;
                if(!is_null($product->instock)) {
                    $images=collect();
                    foreach ($product->instock->getMedia('product') as $img){
                        $images->push($img->getFullUrl());
                    }
                    $product['images']= $images;
                    $product['description']= $product['instock']['description'];
                    $product['basic_price']= $product['instock']['basic_price'];
                    unset($product['instock']['media']);
                    unset($product['instock']['shop_id']);
                    unset($product['instock']['user_id']);
                    unset($product['instock']['product_id']);
                    unset($product['instock']['size_id']);
                    unset($product['instock']['description']);
                    unset($product['instock']['created_at']);
                    unset($product['instock']['updated_at']);
                }
                $product['category'] = $product->category;
                $product['shop'] = $product->shop;

                unset($product['size_id']);
                unset($product['price']);
                unset($product['shop_id']);
                unset($product['user_id']);
                unset($product['category_id']);
                unset($product['created_at']);
                unset($product['updated_at']);
                unset($product['category']['created_at']);
                unset($product['category']['updated_at']);
                unset($product['shop']['user_id']);
                unset($product['shop']['created_at']);
                unset($product['shop']['updated_at']);
//                unset($product['size']['created_at']);
//                unset($product['size']['updated_at']);
                return $product;
            });
        }

        return $this->response($products);
    }

    public function getProductByID($product_id)
    {
        $product = Product::whereId($product_id)->with('instock')->has('instock')->first();
        if(is_null($product)) {
            return $this->ErrorResponse(400,'No product found ..!');
        }
//        $product['size']= $product->size;
        $size_arr = array();
        if(count($product['sizes']) != 0) {
            foreach ($product['sizes'] as $size) {
                $sz = Size::whereId($size)->first();
                array_push($size_arr, $sz);
            }
        }
        $product['sizes'] = $size_arr;
        if(!is_null($product->instock)) {
            $images=collect();
            foreach ($product->instock->getMedia('product') as $img){
                $images->push($img->getFullUrl());
            }
            $product['images']= $images;
            $product['description']= $product['instock']['description'];
            $product['basic_price']= $product['instock']['basic_price'];
            unset($product['instock']['media']);
            unset($product['instock']['shop_id']);
            unset($product['instock']['user_id']);
            unset($product['instock']['product_id']);
            unset($product['instock']['size_id']);
            unset($product['instock']['description']);
            unset($product['instock']['created_at']);
            unset($product['instock']['updated_at']);
        }
        $product['category'] = $product->category;
        $product['shop'] = $product->shop;

        unset($product['size_id']);
        unset($product['price']);
        unset($product['shop_id']);
        unset($product['user_id']);
        unset($product['category_id']);
        unset($product['created_at']);
        unset($product['updated_at']);
        unset($product['category']['created_at']);
        unset($product['category']['updated_at']);
        unset($product['shop']['user_id']);
        unset($product['shop']['created_at']);
        unset($product['shop']['updated_at']);
//        unset($product['size']['created_at']);
//        unset($product['size']['updated_at']);
        return $this->response($product);
    }

    public function productFetchWithType(Request $request, $type_name)
    {
        $query = Product::query();
        if($type_name == 'isi') {
            $query = $query->latest()->with('shop', 'size', 'instock')->whereHas('instock', function ($q) {
               $q->where('brand_type', 'isi');
            });
        }else{
            $query = $query->latest()->with('shop', 'size', 'instock')->whereHas('instock', function ($q) {
                $q->where('brand_type', '!=', 'isi');
            });
        }
        if(!is_null($request->get('limit'))) {
            $products= tap($query->paginate($request->limit)->appends('limit', $request->limit))->transform(function($product){
//                $product['size']= $product->size;
                $size_arr = array();
                if(count($product['sizes']) != 0) {
                    foreach ($product['sizes'] as $size) {
                        $sz = Size::whereId($size)->first();
                        array_push($size_arr, $sz);
                    }
                }
                $product['sizes'] = $size_arr;
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
            $products= $query->get()->map(function($product){
//                $product['size']= $product->size;
                $size_arr = array();
                if(count($product['sizes']) != 0) {
                    foreach ($product['sizes'] as $size) {
                        $sz = Size::whereId($size)->first();
                        array_push($size_arr, $sz);
                    }
                }
                $product['sizes'] = $size_arr;
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

    public function productListSeller(Request $request){
        if(!is_null($request->get('limit'))) {
            $products= tap(Product::where('user_id', auth()->id())->latest()->with('shop', 'size', 'instock')->paginate($request->limit)->appends('limit', $request->limit))->transform(function($product){
//                $product['size']= $product->size;
                $size_arr = array();
                if(count($product['sizes']) != 0) {
                    foreach ($product['sizes'] as $size) {
                        $sz = Size::whereId($size)->first();
                        array_push($size_arr, $sz);
                    }
                }
                $product['sizes'] = $size_arr;
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
//                $product['size']= $product->size;
                $size_arr = array();
                if(count($product['sizes']) != 0) {
                    foreach ($product['sizes'] as $size) {
                        $sz = Size::whereId($size)->first();
                        array_push($size_arr, $sz);
                    }
                }
                $product['sizes'] = $size_arr;
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

    public function productDelete(Request $request, $product_id)
    {
        $product = Product::whereId($product_id)->first();
        if(is_null($product)) {
            return $this->ErrorResponse(400,'No product found ..!');
        }
        if(!is_null($request->get('restrictmode'))) {
            if($request->restrictmode == 'true') {
                if(Instock::where('product_id', $product->id)->exists()) {
                    return $this->ErrorResponse(400,'You have stock of this product. ..!');
                }
            }

        }
        $product->delete();
        return $this->SuccessResponse(200,'Product deleted successfully ..!');
    }

    public function add_offer(Request $request){
        $validate= Validator::make($request->all(),[
            'name'=>'required',
            'banner.*'=>'image|mimes:jpg,jpeg,png,bmp|max:3000'
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }
        $offer= Offer::create([
            'name'=>$request->name,
            'description'=>$request->description,
        ]);
        if(!$offer){
            return $this->ErrorResponse(400,'Somethings went wrong while add product ..!');
        }

        if($request->hasFile('banner')){
            $offer->addMedia($request->banner)->toMediaCollection('offer');

        }

        return $this->SuccessResponse(200,'Offer added successfully ..!');
    }

    public function offerList(Request $request){
        if(!is_null($request->get('limit'))) {
            $products= tap(Offer::latest()->with('products')->paginate($request->limit)->appends('limit', $request->limit))->transform(function($listing){
                $listing['banner'] = $listing->getFirstMediaUrl('offer');
                unset($listing['media']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                foreach($listing['products'] as $product) {
                    unset($product['created_at']);
                    unset($product['updated_at']);
                }
                return $listing;
            });
        }else{
            $products= Offer::latest()->with('products')->get()->map(function($listing){
//                $product['size']= $product->size;
//                $product['image']= $product->instock;
//                unset($product['size_id']);
                $listing['banner'] = $listing->getFirstMediaUrl('offer');
                unset($listing['media']);
                unset($listing['created_at']);
                unset($listing['updated_at']);
                foreach($listing['products'] as $product) {
                    unset($product['created_at']);
                    unset($product['updated_at']);
                }

                return $listing;
            });
        }

        return $this->response($products);
    }

    public function offerAddBulkProduct(Request $request)
    {
        $validate=Validator::make($request->all(),[
            'product_id'=>'required',
            'offer_id'=>'required',
        ]);
        if($validate->fails()){
            return  $this->ErrorResponse(400,$validate->messages());
        }
        $offer = Offer::whereId($request->offer_id)->first();
        if(is_null($offer)) {
            return  $this->ErrorResponse(400,'Offer not found ..!');
        }
        foreach($request->product_id as $product_id) {
            $product=Product::whereId($product_id)->first();
            if(!is_null($product)) {
                $product->offer_id = $offer->id;
                $product->save();
            }
        }
        return $this->SuccessResponse(200,'Offer added to products ..!');
    }

    public function productSearch(Request $request)
    {
        if(is_null($request->get('keyword'))) {
            return  $this->ErrorResponse(400,'No keyword found for searching ..!');
        }
//        if(count(count_chars($request->keyword, 1)) < 3) {
//            return  $this->ErrorResponse(400,'Minimum you have to 3 keyword using ..!');
//        };
        $products = Product::where('name', 'like', "%$request->keyword%")
                    ->with('instock')
                    ->orWhereHas('instock', function ($stock) use($request) {
                        $stock->where('basic_price', 'like', "%$request->keyword%");
                        $stock->orWhere('description', 'like', "%$request->keyword%");
                    })
                    ->get();
        return $this->SuccessResponse(200,'Search result ..!', $products);
    }

}

