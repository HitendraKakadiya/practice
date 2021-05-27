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
use Carbon\Carbon;

class ChangePasswordController extends Controller
{
    public function change_profile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone' => 'required | min:10 | max:10',
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
            $user->user_img = $path;
            $user->update();
            $success['name'] = $user['name'];
            $success['phone'] = $user['phone'];
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
        $date1 = Carbon::now()->toDateTimeString();
        $user = User::all();
        foreach ($user as $item) {
            $date2 = $item['otpExpTime'];
            if ($date1 >= $date2) {
                User::where('otpExpTime', $date2)->update([
                    'OTP' => null,
                    'otpExpTime' => null,
                ]);
            }
        }
        try {
            $email = $request->email;
            $user = DB::Table('users')
                ->select('name')
                ->where('email', $email)
                ->first();

            if (!empty($user)) {
                $pass_random = rand(1111, 9999);
                $data = ['Password' => $pass_random, 'user_email' => $email];
                User::where('email', $email)->update([
                    'OTP' => $pass_random,
                    'otpExpTime' => Carbon::now()
                        ->addMinutes(15)
                        ->toDateTimeString(),
                ]);

                Mail::send('mail', $data, function ($message) use ($email) {
                    $message
                        ->to($email, 'Stocard User')
                        ->subject('Forgot Password Information');
                    $message->from('stocard@gmail.com', 'Stocard Owner');
                });
            } else {
                return $this->sendError(
                    'Check your Mail-ID',
                    'Email Id Not Found'
                );
            }
            return $this->sendResponse(
                'OTP has sent to your Email',
                'Check Your Mail'
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
        ];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        if (
            Hash::check(request('old_password'), Auth::user()->password) ==
            false
        ) {
            return $this->sendError('Unauthorised.', [
                'error' => 'Old Password is not Match. Please Check Once!!!',
            ]);
        } elseif (
            Hash::check(request('new_password'), Auth::user()->password) == true
        ) {
            return $this->sendError('Unauthorised.', [
                'error' =>
                    'Please enter a Password Which is not Similar then Current Password!!!',
            ]);
        } else {
            User::where('id', $userid)->update([
                'password' => Hash::make($input['new_password']),
            ]);
            $user = User::where('id', $userid)->first();
            $success['email'] = $user['email'];
            return $this->sendResponse(
                'Password Change Successfully',
                $success
            );
        }
    }

    public function otp_verify(Request $request)
    {
        $date1 = Carbon::now()->toDateTimeString();
        $user = User::all();
        foreach ($user as $item) {
            $date2 = $item['otpExpTime'];
            if ($date1 >= $date2) {
                User::where('otpExpTime', $date2)->update([
                    'OTP' => null,
                    'otpExpTime' => null,
                ]);
            }
        }
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $otp = $request->otp;
        $email = $request->email;

        $user = User::where('email', $email)
            ->where('OTP', $otp)
            ->first();
        if (!empty($user)) {
            return $this->sendResponse('OTP Match Successfully.');
        } else {
            return $this->sendError(
                'Error',
                'OTP does not match or Expired. Please Check Again!!!'
            );
        }
    }

    public function create_new_password(Request $request)
    {
        $date1 = Carbon::now()->toDateTimeString();
        $user = User::all();
        foreach ($user as $item) {
            $date2 = $item['otpExpTime'];
            if ($date1 >= $date2) {
                User::where('otpExpTime', $date2)->update([
                    'OTP' => null,
                    'otpExpTime' => null,
                ]);
            }
        }

        $validator = Validator::make($request->all(), [
            'new_password' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $new_password = bcrypt($request->new_password);
        $email = $request->email;

        $user = User::where('email', $email)->update([
            'password' => $new_password,
        ]);
        if ($user) {
            User::where('email', $email)->update([
                'OTP' => null,
                'otpExpTime' => null,
            ]);
            return $this->sendResponse('New Password Change Successfully.');
        } else {
            return $this->sendError('Something want Wrong!!!');
        }
    }
}
