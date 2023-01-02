<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function size(){
        $sizes= Size::all();
        return  $this->SuccessResponse(200,'Data fetch successfully ..!',$sizes);
    }
}
