<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;

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
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::group(['prefix'=>'seller'],function(){
        Route::post('product/add',[\App\Http\Controllers\Api\ProductController::class,'add_product']);
        Route::get('product/name',[\App\Http\Controllers\Api\ProductController::class,'product_names']);
        Route::get('size',[\App\Http\Controllers\Api\SizeController::class,'size']);
        Route::post('shop',[\App\Http\Controllers\Api\ShopController::class,'shop']);
        Route::get('my/shop',[\App\Http\Controllers\Api\ShopController::class,'get_self_shop']);
        Route::post('stock/add',[\App\Http\Controllers\Api\InstockController::class,'add_stock']);
        Route::get('stock/list',[\App\Http\Controllers\Api\InstockController::class,'get_stock']);
        Route::post('stock/delete/{id}',[\App\Http\Controllers\Api\InstockController::class,'delete_stock']);
        Route::post('stock/update',[\App\Http\Controllers\Api\InstockController::class,'update_stock']);
    });

    Route::group(['prefix' =>'buyer'],function(){
       Route::get('banner/list',[\App\Http\Controllers\Api\BannerController::class,'get_banner']);
        Route::get('product/name',[\App\Http\Controllers\Api\ProductController::class,'product_names']);
        Route::get('stock/list',[\App\Http\Controllers\Api\InstockController::class,'buyer_product_list']);
        Route::post('cart',[OrderController::class,'cart']);
        Route::post('checkout',[OrderController::class,'checkout']);
    });

    Route::group(['prefix' =>'admin'],function(){
       Route::get('home',[HomeController::class,'home']);
       Route::get('product/name',[ProductController::class,'product_names']);
       Route::post('product/add',[ProductController::class,'add_product']);
       Route::get('customers',[UserController::class,'all_customers']);
       Route::get('drivers',[UserController::class,'all_drivers']);
       Route::get('sellers',[UserController::class,'all_sellers']);
    });

});
Route::post('register',[\App\Http\Controllers\Api\AuthController::class,'register']);
Route::post('login',[\App\Http\Controllers\Api\AuthController::class,'login']);
Route::post('validate/otp',[\App\Http\Controllers\Api\AuthController::class,'verify_otp']);
Route::post('logout',[\App\Http\Controllers\Api\AuthController::class,'logout']);
Route::get('authentication',[AuthController::class,'authentication'])->name('authentication');
