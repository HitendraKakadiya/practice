<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\User;
use DB;
use Mail;
use File;
class ChangePasswordController extends Controller
{
    public function change_profile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone' => 'required | min:10 | max:10',
                'pin' => 'required | min:4 | max:4',
                'user_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    'Validation Error.',
                    $validator->errors()
                );
            }

            $user = Auth::user();
            $old_image = str_replace(
                'http://stocard.project-demo.info/upload/user_img/',
                '',
                $user['user_img']
            );
            File::delete(public_path('upload/user_img/' . $old_image));
            $image = $request->file('user_img');
            $new_name = rand() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('/upload/user_img'), $new_name);
            $path = 'http://stocard.project-demo.info/upload/user_img/';
            $path .= $new_name;

            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->pin = $request->pin;
            $user->user_img = $path;
            $user->update();
            $success['name'] = $user['name'];
            $success['phone'] = $user['phone'];
            $success['pin'] = $user['pin'];
            $success['Image'] = $user['user_img'];

            return response()->json([
                'status' => 'True',
                'message' => 'Profile Updated',
                'data' => $success,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'False',
                'message' => $e->getMessage(),
                'data' => [],
            ]);
        }
    }

    public function forgot_password(Request $request)
    {
        try {
            $email = $request->email;
            $user = DB::Table('users')
                ->select('name')
                ->where('email', $email)
                ->get();

            if (sizeof($user) != 0) {
                $pass_random = rand(1111, 9999);
                $data = ['Password' => $pass_random, 'user_email' => $email];
                User::where('email', $email)->update([
                    'password' => Hash::make($pass_random),
                ]);

                Mail::send('mail', $data, function ($message) use ($email) {
                    $message
                        ->to($email, 'Stocard User')
                        ->subject('Forgot Password Information');
                    $message->from('stocard@gmail.com', 'Stocard Owner');
                });
            } else {
                return $this->sendError(
                    'Email Id Not Found',
                    'Check your Mail-ID'
                );
            }
            return $this->sendResponse(
                'Check Your Mail',
                'Password has sent to your Email'
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'False',
                    'message' => $e->getMessage(),
                    'data' => [],
                ],
                500
            );
        }
    }

    public function change_password(Request $request)
    {
        $input = $request->all();
        $userid = Auth::guard('api')->user()->id;
        $rules = [
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password',
        ];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = [
                'status' => true,
                'message' => $validator->errors()->first(),
            ];
        } else {
            try {
                if (
                    Hash::check(
                        request('old_password'),
                        Auth::user()->password
                    ) == false
                ) {
                    $arr = [
                        'status' => false,
                        'message' => 'Check your old password.',
                    ];
                } elseif (
                    Hash::check(
                        request('new_password'),
                        Auth::user()->password
                    ) == true
                ) {
                    $arr = [
                        'status' => false,
                        'message' =>
                            'Please enter a password which is not similar then currunt password.',
                    ];
                } else {
                    User::where('id', $userid)->update([
                        'password' => Hash::make($input['new_password']),
                    ]);
                    $arr = [
                        'status' => true,
                        'message' => 'Password updated Successfully',
                    ];
                    return response()->json([
                        'status' => true,
                        'message' => 'Changed Password Successfully',
                    ]);
                }
            } catch (\Exception $e) {
                if (isset($e->errorInfo[2])) {
                    $msg = $e->errorInfo[2];
                } else {
                    $msg = $e->getMessage();
                }
                $arr = ['status' => false, 'message' => $msg];
                return response()->json([
                    'status' => false,
                    'message' => $arr,
                ]);
            }
        }
        return $this->sendError('Unaurthorized.', ['error' => 'Unaurthorized']);
    }
}
