<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Http\Controllers\BaseController;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'phone' => 'required | min:10 | max:10',
            'pin' => 'required | min:4 | max:4',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $image = $request->file('user_img');
        $new_name = rand() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('/images/user_img'), $new_name);
        $path = 'http://192.168.43.133:8000/images/user_img/';
        $path .= $new_name;

        $input = $request->all();
        $input['user_img'] = $path;
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;
        $success['email'] = $user->email;
        $success['pin'] = $user->pin;
        $success['phone'] = $user->phone;
        $success['Image'] = $user->user_img;

        return $this->sendResponse($success, 'User register successfully.');
    }

    public function login(Request $request)
    {
        if (
            Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
            ])
        ) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->accessToken;
            $success['name'] = $user->name;
            $success['email'] = $user->email;
            $success['pin'] = $user->pin;
            $success['phone'] = $user->phone;
            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', [
                'error' => 'Unauthorised',
            ]);
        }
    }
}
