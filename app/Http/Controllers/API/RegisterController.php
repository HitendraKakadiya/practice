<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Http\Controllers\BaseController;
use File;

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
            'user_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'device_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $image = $request->file('user_img');
        $new_name = rand() . '.' . $image->getClientOriginalExtension();
        $path = public_path() . '/upload/user_img';
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }
        $image->move(public_path('/upload/user_img'), $new_name);
        $path = 'http://stocard.project-demo.info/upload/user_img/';
        $path .= $new_name;

        $input = $request->all();
        $input['user_img'] = $path;
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['id'] = $user->id;
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;
        $success['email'] = $user->email;
        $success['pin'] = $user->pin;
        $success['phone'] = $user->phone;
        $success['Image'] = $user->user_img;
        $success['device_id'] = $user->device_id;

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
            $validator = Validator::make($request->all(), [
                'device_id' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendError(
                    'Validation Error.',
                    $validator->errors()
                );
            }

            User::where('id', Auth::user()->id)->update([
                'device_id' => $request->device_id,
            ]);
            $user = Auth::user();
            $success['id'] = $user->id;
            $success['token'] = $user->createToken('MyApp')->accessToken;
            $success['name'] = $user->name;
            $success['email'] = $user->email;
            $success['pin'] = $user->pin;
            $success['phone'] = $user->phone;
            $success['Image'] = $user->user_img;
            $success['deviec_id'] = $request->device_id;
            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', [
                'error' => 'Unauthorised',
            ]);
        }
    }
}
