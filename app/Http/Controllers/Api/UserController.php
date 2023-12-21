<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Exception;
use Auth;

class UserController extends Controller
{
    public function createUser(Request $request){

        $validator = Validator::make($request->all(),[
            'name'=> 'required |string',
            'email'=>'required |string|unique:users',
            'phone'=>'required |numeric|digits:10',
            'password'=>'required|min:6 '
        ]);
        if ($validator->fails()) {

            $result = array(['status'=> false, 'message'=>'Validation error occured', 'error_message'=>$validator->errors()]);
            return response()->json($result, 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'password'=>bcrypt($request->password), 
        ]);
        if ($user->id) {
            $result = array('status'=> true, 'message'=>'user created successfully', 'data'=> $user);
            $resposnceCode = 200;
        }else{
            $result = array('status'=>false, 'message'=>'user not created successfully');
            $resposnceCode = 400;
        }
        return response()->json($result, $resposnceCode);
    }

    public function getUser(){
        try{
            $user = User::all();
            $result = array('status'=>true, 'message'=>count($user)."users fetched",'data'=>$user);
            $resposnceCode = 200;
            return response()->json($result, $resposnceCode);
        }catch(Exception $e){
            $result = array('status'=>false, 'message'=>'API failed due to an error',
            'error' => $e->getMessage());
            return response()->json($result,500);
        }
    }

    public function getUserDetail($id){
        $user = User::find($id);
        if (!$user) { 
            return response()->json(['status'=>false, 'message'=>'user not  found'], 404);
        }
        $result = array(['status'=>true, 'message'=>'user fetched', 'data'=>$user]);
        $resposnceCode = 200;
        return response()->json($result, $resposnceCode);
    }

    public function updateUser(Request $request, $id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status'=>false, 'message'=>'user not found'], 404);
        }
        $validator = Validator::make($request->all(),[
            'name'=> 'required|string',
            'email'=> 'required|string|unique:users,email,'. $id,
            'phone'=> 'required|numeric|digits:10',
        ]);
        if ($validator->fails()) {
            $result = array(['status'=>true, 'message'=>'Validator error occured', 
            'error_message'=> $validator->errors()]);
            return response()->json($result, 400);
        }

            $user->name= $request->name;
            $user->email= $request->email;
            $user->phone= $request->phone;
            $user->save();

       $result = array('status'=>true, 'message'=>'user Updated Successfully','data'=>$user);
       $resposnceCode = 200;
       return response()->json($result,$resposnceCode);

    }

    public function deleteUser( $id){

        $user = User::find($id);
        if (!$user){
            return response()->json(['status'=>false, 'message'=>'user not Found'], 404);
        }
        $user->delete();
        $result = array('status'=>true, 'message'=>'user Deleted Successfully.');
        $resposnceCode = 200;
        return response()->json($result, $resposnceCode);

    }

    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required',
            'password' =>'required'
        ]);

        if ($validator->fails()){
            return response()->json(['stauts'=> false, 'message'=> 'validation error occured', 'errors'=>$validator->errors(),400 ]);
        }

        $credentials = $request->only('email','password');

       if (Auth::attempt($credentials)) {

        $user = Auth::user();
        $token = $user->createToken('MyApp')->accessToken;
        return response()->json(['status'=>true, 'message'=>'User Login Successfully', 'token'=> $token], 200);
       } 
       return response()->json(['status'=>false, 'message'=>'Login Credentials Invalid'], 401);

    }

    public function unauthenticate(){
       return response()->json(['status'=>false, 'message'=>'Only Authorised user can access', 'error' => 'Un-Authenticated'], 401);

    }

    public function logout(){
        $user = Auth::user();
        $user->tokens->each(function($token, $key){
            $token->delete();

        });
      //$user = Auth::guard('api')->user();
        return response()->json(['status'=>true, 'message'=>'User Deleted Successfully'], 200);
 
     }


}
