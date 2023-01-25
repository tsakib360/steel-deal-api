<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\SizeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Category
Route::get('category/list',[CategoryController::class,'getCategory']);

//Search
Route::get('search/product',[ProductController::class,'productSearch']);

//Products
Route::get('product/all',[ProductController::class,'product_names']);
Route::get('product/type/{type_name}',[ProductController::class,'productFetchWithType']);

//Forget Password
Route::post('forget-password',[\App\Http\Controllers\Api\AuthController::class,'forgetPassword']);
Route::post('reset-password',[\App\Http\Controllers\Api\AuthController::class,'resetPassword']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('logout',[\App\Http\Controllers\Api\AuthController::class,'logout']);
    Route::group(['prefix'=>'seller'],function(){
        //Product
        Route::post('product/add',[ProductController::class,'add_product']);
        Route::post('product/update/{product_id}',[ProductController::class,'updateProduct']);
        Route::get('product/list',[ProductController::class,'productListSeller']);
        Route::post('product/delete/{product_id}',[ProductController::class,'productDelete']);


        Route::get('size',[\App\Http\Controllers\Api\SizeController::class,'size']);
        Route::post('shop',[\App\Http\Controllers\Api\ShopController::class,'shop']);
        Route::get('my/shop',[\App\Http\Controllers\Api\ShopController::class,'get_self_shop']);
        Route::post('stock/add',[\App\Http\Controllers\Api\InstockController::class,'add_stock']);
        Route::get('stock/list',[\App\Http\Controllers\Api\InstockController::class,'get_stock']);
        Route::post('stock/delete/{id}',[\App\Http\Controllers\Api\InstockController::class,'delete_stock']);
        Route::post('stock/update',[\App\Http\Controllers\Api\InstockController::class,'update_stock']);

        //Category
        Route::post('category/add',[CategoryController::class,'addCategorySeller']);
        Route::get('category/list',[CategoryController::class,'getCategorySeller']);

        //Sub Category
        Route::post('sub-category/add',[CategoryController::class,'addSubCategorySeller']);
        Route::get('sub-category/list',[CategoryController::class,'getSubCategory']);

        //Shop
        Route::get('shop-time',[ShopController::class,'getShopTime']);
        Route::post('update-shop-time',[ShopController::class,'updateShopTime']);
        Route::post('update-status',[ShopController::class,'updateShopStatus']);

        //Offer
        Route::get('offer/list',[ProductController::class,'offerList']);
        Route::post('offer/add/product/bulk',[ProductController::class,'offerAddBulkProduct']);

        //Orders
        Route::get('order/items',[OrderController::class,'orderItemListByShopID']);
    });

    Route::group(['prefix' =>'customer'],function(){
       Route::get('banner/list',[\App\Http\Controllers\Api\BannerController::class,'get_banner']);
        Route::get('product/name',[\App\Http\Controllers\Api\ProductController::class,'product_names']);
        Route::get('stock/list',[\App\Http\Controllers\Api\InstockController::class,'buyer_product_list']);
        Route::post('cart',[OrderController::class,'cart']);
        Route::post('checkout',[OrderController::class,'checkout']);

        //Category
        Route::get('category/list',[CategoryController::class,'getCategorySeller']);

        //Product Size
        Route::post('size/request',[SizeController::class,'sizeRequestByUser']);
    });

    Route::group(['prefix' =>'admin'],function(){
       Route::get('home',[HomeController::class,'home']);
       Route::get('product/name',[ProductController::class,'product_names']);
       Route::post('product/add',[ProductController::class,'add_product']);
       Route::get('customers',[UserController::class,'all_customers']);
       Route::get('drivers',[UserController::class,'all_drivers']);
       Route::get('sellers',[UserController::class,'all_sellers']);
       Route::get('orders',[OrderController::class,'orderList']);
       Route::get('transactions',[TransactionController::class,'transactionList']);

       //Offer
       Route::post('offer/add',[ProductController::class,'add_offer']);
    });

});
Route::post('register',[\App\Http\Controllers\Api\AuthController::class,'register']);
Route::post('login',[\App\Http\Controllers\Api\AuthController::class,'login']);
Route::post('validate/otp',[\App\Http\Controllers\Api\AuthController::class,'verify_otp']);
Route::get('authentication',[AuthController::class,'authentication'])->name('authentication');

Route::group(['prefix'=>'seller'],function(){
    Route::post('login',[AuthController::class,'loginSeller']);
    Route::post('validate/otp',[AuthController::class,'verify_otp_seller']);
    Route::post('logout',[AuthController::class,'logout']);
});
