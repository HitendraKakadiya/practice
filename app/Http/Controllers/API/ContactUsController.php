<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use Validator;

class ContactUsController extends Controller
{
    public function contactus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['subject'] = $request->subject;
        $data['description'] = $request->description;
        $email = $data['email'];

        Mail::send('contactus', $data, function ($message) use ($email) {
            $message->to($email, 'Stocard User')->subject('Ask for Queries');
            $message->from('stocard@gmail.com', 'Stocard Support');
        });
        return $this->sendResponse('Thank you For your Contact!!!');
    }
}
