<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home()
    {
        $data['total_order'] = Order::count();
        $data['order_amount'] = Order::sum('grand_total');
        $data['total_customers'] = User::where('role', 4)->count();
        $data['total_categories'] = 0;
        $data['total_products'] = Product::count();
        return $this->SuccessResponse(200, 'Data successfully fetched', $data);
    }
}
