<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNotification;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function cart(Request $request)
    {
        DB::beginTransaction();
        try {
            $validate= Validator::make($request->all(),[
                'product_id'=>'required',
                'stock_id'=>'required',
                'size_id'=>'required',
                'qty'=>'required',
                'price'=>'required',
            ]);
            if($validate->fails()){
                return  $this->ErrorResponse(400,$validate->messages());
            }

            if($request->qty <= 0) {
                return $this->ErrorResponse(400,'You must be selected one product ..!');
            }
            $stock = Instock::where('id', $request->stock_id)->has('product')->first();
            if(is_null($stock)) {
                return $this->ErrorResponse(400,'No product found ..!');
            }
            if($stock->basic_price != $request->price) {
                return $this->ErrorResponse(400,'Price is changed ..!');
            }
            if(Auth::user()->role != 4) {
                return $this->ErrorResponse(400,'You are not a customer ..!');
            }

            $prev_cart = OrderItem::where('user_id', Auth::id())->where('stock_id', $request->stock_id)->where('order_id', null)->first();
            if(is_null($prev_cart)) {
                $cart = OrderItem::create([
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id,
                    'stock_id' => $request->stock_id,
                    'size_id' => $request->size_id,
                    'qty' => $request->qty,
                    'price' => $request->price,
                    'total' => $request->qty * $request->price,
                ]);
            }else {
                $cart = $prev_cart->update([
                    'size_id' => $request->size_id,
                    'qty' => $request->qty,
                    'price' => $request->price,
                    'total' => $request->qty * $request->price,
                ]);
            }

            if(!$cart){
                return $this->ErrorResponse(400,'Somethings went wrong while add cart ..!');
            }
            DB::commit();
            return $this->SuccessResponse(200,'Cart successfully ..!');
        }catch (\Exception $e) {
            DB::rollBack();
            return $this->ErrorResponse(400,'Somethings went wrong ..!');
        }

    }
    public function checkout(Request $request)
    {
        DB::beginTransaction();
        try {
            $validate= Validator::make($request->all(),[
                'subtotal'=>'required',
                'delivery_charge'=>'required',
                'grand_total'=>'required',
                'payment_method'=>'required',
            ]);
            if($validate->fails()){
                return  $this->ErrorResponse(400,$validate->messages());
            }

            if(OrderItem::where('user_id', Auth::id())->where('order_id', null)->count() == 0) {
                return $this->ErrorResponse(400,'No product found in your cart ..!');
            }
            $item_subtotal = OrderItem::where('user_id', Auth::id())->where('order_id', null)->sum('total');
            if($item_subtotal != $request->subtotal) {
                return $this->ErrorResponse(400,'Subtotal is not correct with cart total ..!');
            }

            $discount = !is_null($request->discount) ? $request->discount : 0;
            $gst = $request->subtotal * (7.5/100);
            $grand_total = ($request->subtotal + $request->delivery_charge + $gst) - $discount;
            if($grand_total != $request->grand_total) {
                return $this->ErrorResponse(400,'Grand total is not correct with cart total ..!');
            }

            if(!in_array($request->payment_method, Order::PAYMENT_METHOD_ARR)) {
                return $this->ErrorResponse(400,'You are not using supported payment method ..!');
            }

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => Auth::id(),
                'subtotal' => $request->subtotal,
                'delivery_charge' => $request->delivery_charge,
                'discount' => $discount,
                'gst' => $gst,
                'grand_total' => $request->grand_total,
                'payment_method' => $request->payment_method,
                'status' => Order::PENDING,
            ]);

            if(!$order){
                return $this->ErrorResponse(400,'Somethings went wrong while add order ..!');
            }
            OrderItem::where('user_id', Auth::id())->where('order_id', null)->update([
                'order_id' => $order->id
            ]);
            OrderNotification::create([
                'order_id' => $order->id,
                'order_status' => $order->status,
                'title' => 'Order placed successfully',
                'comment' => 'An order is placed. Order ID is: '.$order->order_number,
            ]);
            DB::commit();
            return $this->SuccessResponse(200,'Order Added successfully ..!');
        }catch (\Exception $e) {
            DB::rollBack();
            return $this->ErrorResponse(400,'Somethings went wrong ..!');
        }

    }

    public function generateOrderNumber() {
        $permitted_chars = '0123456789';
        $number =  substr(str_shuffle($permitted_chars), 0, 4);
        if(Order::where('order_number', $number)->exists()) {
            return $this->generateOrderNumber();
        }
        return $number;
    }

    public function orderList(Request$request)
    {
        if(!is_null($request->get('limit'))) {
            $orders= tap(Order::latest()->paginate($request->limit)->appends('limit', $request->limit))->transform(function($order){
                $order['order_date'] = Carbon::parse($order->created_at)->toDateString();
                $order['order_status'] = $order->orderStatus();
                return $order;
            });
        }else{
            $orders= Order::latest()->get()->map(function($order){
                $order['order_date'] = Carbon::parse($order->created_at)->toDateString();
                $order['order_status'] = $order->orderStatus();
                return $order;
            });
        }

        return $this->response($orders);
    }

    public function orderItemListByShopID()
    {
        $shop = Shop::where('user_id', Auth::id())->first();
        if(is_null($shop)) {
            return $this->ErrorResponse(400,'We did not find your shop. Please check your shop is available or not ..!');
        }
        $order_items = OrderItem::with('product', 'product.instock')->whereHas('product', function ($query) use($shop) {
            $query->where('shop_id', $shop->id);
        })->get()->map(function($order_item){
            $order_item['size']= $order_item->size;
            $order_item['buyer']= $order_item->user;
            $order_item['order']= $order_item->order;
            if(!is_null($order_item->product->instock)) {
                $images=collect();
                foreach ($order_item->product->instock->getMedia('product') as $img){
                    $images->push($img->getFullUrl());
                }
                $order_item['product']['instock']['images']= $images;
                unset($order_item['product']['instock']['media']);
            }
            unset($order_item['product']['size_id']);
            unset($order_item['product']['size']);
            unset($order_item['order_id']);
            unset($order_item['user_id']);
            unset($order_item['shop_id']);
            unset($order_item['user']);
            unset($order_item['size_id']);
            unset($order_item['product_id']);
            unset($order_item['stock_id']);
            return $order_item;
        });
        return $this->SuccessResponse(200,'Order Items Fetched Successfully ..!', $order_items);
    }

    public function trackOrder($order_id)
    {
        $order = Order::whereId($order_id)->first();
        if(is_null($order)) {
            return $this->ErrorResponse(400,'No order found! ..!');
        }
        $track_order = OrderNotification::where('order_id', $order_id)->latest()->get();
        return $this->SuccessResponse(200,'Order Items Fetched Successfully ..!', $track_order);

    }
}
