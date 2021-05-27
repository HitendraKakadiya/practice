<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\CardShare;
use App\storecard;
use App\storedata;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Validator;

class CardShareController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function random_code(Request $request)
    {
        $date1 = Carbon::now()->toDateTimeString();
        $card = CardShare::all();
        foreach ($card as $item) {
            $date2 = $item['exptime'];
            if ($date1 >= $date2) {
                CardShare::where('exptime', $date2)->delete();
            }
        }
        try {
            $validator = Validator::make($request->all(), [
                'card_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    'Validation Error',
                    $validator->errors()
                );
            }
            $user_id = Auth::guard('api')->user()->id;
            $card_id = $request->input('card_id');
            $card_data = storecard::where('id', $card_id)->first();
            if (!$card_data->isEmpty()) {
                $random = Str::random(6);

                $data = CardShare::create([
                    'user_id' => $user_id,
                    'share_code' => $random,
                    'card_id' => $card_id,
                    'exptime' => Carbon::now()
                        ->addMinutes(720)
                        ->toDateTimeString(),
                ]);

                $success['share_code'] = $data['share_code'];
                $success['exptime'] = $data['exptime'];
                return $this->sendResponse(
                    'Code Generated Successfully. This code is Expire in 12 hours.',
                    $success
                );
            } else {
                return $this->sendError('Card Not Found');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function add_share_card(Request $request)
    {
        $date1 = Carbon::now()->toDateTimeString();
        $card = CardShare::all();
        foreach ($card as $item) {
            $date2 = $item['exptime'];
            if ($date1 >= $date2) {
                CardShare::where('exptime', $date2)->delete();
            }
        }
        $validator = Validator::make($request->all(), [
            'share_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }
        $user_id = Auth::guard('api')->user()->id;
        $share_code = $request->input('share_code');
        $card = CardShare::where('share_code', $share_code)->first();
        if (!$card->isEmpty()) {
            $card_id = $card['card_id'];
            $card_data = storecard::where('id', $card_id)->first();
            $available = storecard::where('user_id', $user_id)
                ->where('cardno', $card_data['cardno'])
                ->first();
            $storedata = storedata::where('id', $card_data['st_id'])->first();
            $store = storedata::where('stname', $storedata['stname'])
                ->where('user_id', $user_id)
                ->first();
        }
        if (!$card->isEmpty()) {
            if (!$store->isEmpty()) {
                if ($available->isEmpty()) {
                    $data = storecard::create([
                        'user_id' => $user_id,
                        'st_id' => $store['id'],
                        'cardname' => $card_data['cardname'],
                        'cardno' => $card_data['cardno'],
                        'rewardpercen' => $card_data['rewardpercen'],
                        'carddetail' => $card_data['carddetail'],
                        'expdate' => $card_data['expdate'],
                        'card_img' => $card_data['card_img'],
                    ]);
                    $success['st_id'] = $data['st_id'];
                    $success['cardname'] = $data['cardname'];
                    $success['cardno'] = $data['cardno'];
                    $success['rewardpercen'] = $data['rewardpercen'];
                    $success['carddetail'] = $data['carddetail'];
                    $success['expdate'] = $data['expdate'];
                    $success['card_img'] = $data['card_img'];

                    return $this->sendResponse(
                        'Shared Card Added Successfully.',
                        $success
                    );
                }
                $fail['cardname'] = $available['cardname'];
                $fail['cardno'] = $available['cardno'];
                return $this->sendError('Card is Already Exist', $fail);
            }
            return $this->sendError(
                'Store ' .
                    $storedata['stname'] .
                    ' is Not Available in your account. Please first Add the Store.',
                ['Error' => 'Operation Failed']
            );
        }
        return $this->sendError(
            'Share Code ' .
                $share_code .
                ' Not Found OR Mis-Matched OR Expired. Please Kindly Check!!!',
            ['Error' => 'Operation Failed']
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
