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
use App\Http\Controllers\Api\TruckController;
use App\Http\Controllers\Api\ProductCounterController;
use App\Http\Controllers\Api\AddressController;

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
Route::post('search/shop/location',[ShopController::class,'searchShopByLocation']);

//Products
Route::get('product/all',[ProductController::class,'product_names']);
Route::get('product/fetch/{product_id}',[ProductController::class,'getProductByID']);
Route::get('product/type/{type_name}',[ProductController::class,'productFetchWithType']);
Route::get('product/category/{category_id}',[CategoryController::class,'getCatOrSubcatProducts']);
Route::get('product/category/{category_id}/type/{type}',[CategoryController::class,'getCatOrSubcatTYpeProducts']);

//Shops
Route::get('shops/{status}',[ShopController::class,'getAllShopsWithType']);

//Orders
Route::get('track-order/{order_id}',[OrderController::class,'trackOrder']);

//Sizes
Route::get('size/list',[\App\Http\Controllers\Api\SizeController::class,'size']);

//Forget Password
Route::post('forget-password',[\App\Http\Controllers\Api\AuthController::class,'forgetPassword']);
Route::post('reset-password',[\App\Http\Controllers\Api\AuthController::class,'resetPassword']);

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('logout',[\App\Http\Controllers\Api\AuthController::class,'logout']);
    Route::post('profile-update',[AuthController::class,'updateProfile']);
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
        Route::post('category/delete/{category_id}',[CategoryController::class,'categoryDelete']);

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

        //Product Counter
        Route::get('product/counter/list',[ProductCounterController::class,'productCounterListBySeller']);
        Route::post('product/counter/update/{counter_id}',[ProductCounterController::class,'productCounterPriceChangeBySeller']);
        Route::post('product/counter/accept/{counter_id}',[ProductCounterController::class,'counterProductAccept']);
    });

    Route::group(['prefix' =>'customer'],function(){
       Route::get('banner/list',[\App\Http\Controllers\Api\BannerController::class,'get_banner']);
        Route::get('product/name',[\App\Http\Controllers\Api\ProductController::class,'product_names']);
        Route::get('stock/list',[\App\Http\Controllers\Api\InstockController::class,'buyer_product_list']);

        //Orders
        Route::get('fetch-cart',[OrderController::class,'getCart']);
        Route::post('cart',[OrderController::class,'cart']);
        Route::post('checkout',[OrderController::class,'checkout']);
        Route::post('reverse-order/{order_id}',[OrderController::class,'reverseOrder']);

        //Category
        Route::get('category/list',[CategoryController::class,'getCategorySeller']);

        //Product Size
        Route::post('size/request',[SizeController::class,'sizeRequestByUser']);

        //Product Counter
        Route::get('product/counter/list',[ProductCounterController::class,'productCounterListByBuyer']);
        Route::post('product/counter',[ProductCounterController::class,'createProductCounter']);
        Route::post('product/counter/update/{counter_id}',[ProductCounterController::class,'productCounterPriceChangeByBuyer']);

        //Address
        Route::get('all-addresses',[AddressController::class,'getUserAddresses']);
        Route::get('address/default',[AddressController::class,'getUserDefaultAddresses']);
        Route::post('address/add',[AddressController::class,'addUserAddress']);
        Route::post('address/update/{address_id}',[AddressController::class,'updateUserAddress']);
        Route::post('address/delete/{address_id}',[AddressController::class,'deleteUserAddress']);
    });

    Route::group(['prefix' =>'admin'],function(){
       Route::get('home',[HomeController::class,'home']);
       Route::get('product/name',[ProductController::class,'product_names']);
       Route::post('product/add',[ProductController::class,'add_product']);
       Route::get('customers',[UserController::class,'all_customers']);
       Route::get('drivers',[UserController::class,'all_drivers']);
       Route::get('sellers',[UserController::class,'all_sellers']);

       //Orders
       Route::get('orders',[OrderController::class,'orderList']);
       Route::post('order/assign-transporter/{order_id}',[OrderController::class,'assignTransporterToOrder']);

       Route::get('transactions',[TransactionController::class,'transactionList']);

       //Offer
       Route::post('offer/add',[ProductController::class,'add_offer']);
    });

    Route::group(['prefix' =>'transporter'],function(){
        //Trucks
       Route::get('all-trucks',[TruckController::class,'allTrucks']);
       Route::post('add-truck',[TruckController::class,'addTruck']);
       Route::post('update-truck/{id}',[TruckController::class,'updateTruck']);
       Route::post('delete-truck/{id}',[TruckController::class,'deleteTruck']);

       //Orders
        Route::get('orders/assigned',[OrderController::class,'assignedOrderList']);
        Route::post('order/start-journey/{order_id}',[OrderController::class,'transporterStartJourney']);
        Route::post('order/complete/{order_id}',[OrderController::class,'orderComplete']);
    });

});
Route::post('register',[\App\Http\Controllers\Api\AuthController::class,'register']);
Route::post('login',[\App\Http\Controllers\Api\AuthController::class,'login']);
Route::post('validate/otp',[\App\Http\Controllers\Api\AuthController::class,'verify_otp']);
Route::get('authentication',[AuthController::class,'authentication'])->name('authentication');

//Route::group(['prefix'=>'seller'],function(){
//    Route::post('login',[AuthController::class,'loginSeller']);
//    Route::post('validate/otp',[AuthController::class,'verify_otp_seller']);
//    Route::post('logout',[AuthController::class,'logout']);
//});
