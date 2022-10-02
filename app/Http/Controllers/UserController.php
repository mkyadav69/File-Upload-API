<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Log, Exception;
use App\Models\User;


class UserController extends Controller
{
    public function register(Request $request){
        try {
            # Global variable
            $response = [];

            # Validation 
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email'=> 'required|email',
                'password'=> 'required'
            ]);
            if ($validator->fails()) {
                return $validator->errors();
            }
            
            # Create user
            $user = User::create([
                'name'=> $request->name,
                'email'=> $request->email,
                'password'=> bcrypt($request->password)
            ]);

            # Check status
            if(!empty($user)){
                $response['status'] = 200;
                $response['message'] = 'User register successfully';
                $token = $user->createToken('API Token')->accessToken;
                $response['token'] = $token;
            }else{
                $response['status'] = 221;
                $response['message'] = 'Something went wrong , please try again later';
            }

            # Generate response
            return response()->json($response);

        } catch (Exception $ex) {
            Log::debug($ex);
        }
    }

    
    public function login(Request $request){
        try {
            $data = $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);
            if (!auth()->attempt($data)) {
                return response(['error_message' => 'Incorrect Details. 
                Please try again']);
            }
            $token = auth()->user()->createToken('API Token')->accessToken;
            return response(['user' => auth()->user(), 'token' => $token]);
        } catch (\Throwable $th) {
            //throw $th;
        }

    }

    public function unaccess(){
        try {
            return response()->json([
                'status'=> 401,
                'message'=>'Unauthorized to access'
            ]);
        } catch (Exception $th) {
            Log::debug($ex);
        }
    }
}
