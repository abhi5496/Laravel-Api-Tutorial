<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function index(){

        $result = array('status'=>true, 'message'=>'Hello From App');
        $responseCode = 200;
        
        return response()->json($result, $responseCode);
    } 
}
