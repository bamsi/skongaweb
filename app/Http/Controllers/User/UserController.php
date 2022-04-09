<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    

    public function authenticate(Request $request)
    {
        $credentials = $request->only('username', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid username or password! Please try again'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
        //get authenticated user details
        $current_user = auth()->user();
        $user = $this->userPermission($current_user->id);
        return response()->json(compact('token','user'));
    }

    public function getAuthenticatedUser()
    {
        try {

            if (!$current_user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }
        $user = $this->userPermission($current_user->id);
        return response()->json(compact('user'));
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'username' => $request->get('username'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    public function userPermission($id){
        return User::with(array('permissions' => function($query) {
                $query->where('active', true)
                ->select('name'); }))
                ->with('institution')
                ->where('id', $id)
                ->first();
    }

    public function changePassword(Request $request){
        $user_id = $request->get('user_id');
        $current_password = $request->get('password');
        $new_password = $request->get('new_password');
        //check if password exist
        $user_exist = User::where('password', Hash::make($current_password))
                      ->where('id', $user_id)
                      ->get();
        if(isset($user_exist)){
           //update password
           User::where('id', $user_id)->update(['password'=>Hash::make($new_password), 'first_login'=>false]);
           $message = 'Password has been changed successfully!';
           return response()->json(compact('message'));
        }else {
            return response()->json(['error' => 'Password is invalid'], 400);         
        }
    }

    /**
     * implement session logout
     */
    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }
   
}
