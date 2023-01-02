<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function SuccessResponse($code,$message,$data=null){
        $response=[];
        if( is_null($data)){
            $response= array(
                'success'=>true,
                'code'=>$code,
                'message'=>$message
            ) ;
        }else{
            $response=array(
                'success'=>true,
                'code'=>$code,
                'message'=>$message ?? ' No Record Found ..!' ,
                'data'=>$data
            );
        }

        return response($response,$code);
    }

    public function ErrorResponse($code,$message){
        $response=array(
            'success'=>false,
            'code'=>$code,
            'message'=>$message,
        );
        return response()->json($response,$code);
    }

    public function get_media($image,$collection_name,$conversion=null){
        return $this->getMedia($image)->toMediaCollection($collection_name,$conversion);
    }
    public function add_media($image,$collection_name,$conversion=null){
        return $this->addMedia($image)->toMediaCollection($collection_name,$conversion);
    }

    public function response($data){
        if($data->count() ==0){
            return $this->SuccessResponse(200,"No record found ..!",$data);
        }
        return $this->SuccessResponse(200,"data fetch successfully ..!",$data);
    }

    public function multiple_image($array,$model,$collection){

            foreach ($array as $media){
                $model->addMedia($media)->toMediaCollection($collection);
            }
            return true;
    }
}
