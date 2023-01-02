<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
     public function get_banner(){
         $banners= Banner::latest()->get()->map(function($banners){
             $banners['banner']= $banners->getFirstMediaUrl('banner');
             unset($banners['media']);
             return $banners;
         });
         return $this->response($banners);
     }
}
