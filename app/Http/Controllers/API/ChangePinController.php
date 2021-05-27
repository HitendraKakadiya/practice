<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use DB;
use Validator;
use Mail;

class ChangePinController extends Controller
{
    public function forgot_pin(Request $request)
    {
        $date1 = Carbon::now()->toDateTimeString();
        $user = User::all();
        foreach ($user as $item) {
            $date2 = $item['pinOtpExpTime'];
            if ($date1 >= $date2) {
                User::where('pinOtpExpTime', $date2)->update([
                    'PinOTP' => null,
                    'pinOtpExpTime' => null,
                ]);
            }
        }
        $email = Auth::guard('api')->user()->email;
        $user = DB::Table('users')
            ->select('name')
            ->where('email', $email)
            ->get();

        if (sizeof($user) != 0) {
            $pass_random = rand(1111, 9999);
            $data = ['Password' => $pass_random, 'user_email' => $email];
            User::where('email', $email)->update([
                'PinOTP' => $pass_random,
                'pinOtpExpTime' => Carbon::now()
                    ->addMinutes(15)
                    ->toDateTimeString(),
            ]);

            Mail::send('pinmail', $data, function ($message) use ($email) {
                $message
                    ->to($email, 'Stocard User')
                    ->subject('Forgot Pin Information');
                $message->from('stocard@gmail.com', 'Stocard Owner');
            });
        } else {
            return $this->sendError('Check your Mail-ID', 'Email Id Not Found');
        }
        return $this->sendResponse(
            'OTP has sent to your Email',
            'Check Your Mail'
        );
    }

    public function otp_verify(Request $request)
    {
        $date1 = Carbon::now()->toDateTimeString();
        $user = User::all();
        foreach ($user as $item) {
            $date2 = $item['pinOtpExpTime'];
            if ($date1 >= $date2) {
                User::where('pinOtpExpTime', $date2)->update([
                    'PinOTP' => null,
                    'pinOtpExpTime' => null,
                ]);
            }
        }
        $validator = Validator::make($request->all(), [
            'pin_otp' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $otp = $request->pin_otp;
        $email = Auth::guard('api')->user()->email;

        $user = User::where('email', $email)
            ->where('PinOTP', $otp)
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

    public function create_new_pin(Request $request)
    {
        $date1 = Carbon::now()->toDateTimeString();
        $user = User::all();
        foreach ($user as $item) {
            $date2 = $item['pinOtpExpTime'];
            if ($date1 >= $date2) {
                User::where('pinOtpExpTime', $date2)->update([
                    'PinOTP' => null,
                    'pinOtpExpTime' => null,
                ]);
            }
        }

        $validator = Validator::make($request->all(), [
            'new_pin' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $new_pin = $request->new_pin;
        $email = Auth::guard('api')->user()->email;

        $user = User::where('email', $email)->update([
            'pin' => $new_pin,
        ]);
        if ($user) {
            User::where('email', $email)->update([
                'PinOTP' => null,
                'pinOtpExpTime' => null,
            ]);
            return $this->sendResponse('New Pin Change Successfully.');
        } else {
            return $this->sendError('Something want Wrong!!!');
        }
    }

    public function change_pin(Request $request)
    {
        $input = $request->all();
        $email = Auth::guard('api')->user()->email;
        $rules = [
            'old_pin' => 'required',
            'new_pin' => 'required|min:4|max:4',
        ];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        if (($request->old_pin == Auth::user()->pin) == false) {
            return $this->sendError(
                'Old Pin is not Match. Please Check Once!!!'
            );
        } elseif (($request->new_pin == Auth::user()->pin) == true) {
            return $this->sendError(
                'Please enter a Pin Which is not Similar then Current Pin!!!'
            );
        } else {
            User::where('email', $email)->update([
                'pin' => $request->new_pin,
            ]);
            return $this->sendResponse('Pin Change Successfully');
        }
    }
}
