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
                dd(CardShare::where('exptime', $date2)->delete());
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
            $card_data = storecard::where('id', $card_id)->get();
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
                    $success,
                    'Code Generated Successfully. This code is Expire in 12 hours.'
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
        $card = CardShare::where('share_code', $share_code)->get();
        if (!$card->isEmpty()) {
            $card_id = $card[0]['card_id'];
            $card_data = storecard::where('id', $card_id)->get();
            $available = storecard::where('user_id', $user_id)
                ->where('cardno', $card_data[0]['cardno'])
                ->get();
            $storedata = storedata::where('id', $card_data[0]['st_id'])->get();
            $store = storedata::where('stname', $storedata[0]['stname'])
                ->where('user_id', $user_id)
                ->get();
        }
        if (!$card->isEmpty()) {
            if (!$store->isEmpty()) {
                if ($available->isEmpty()) {
                    $data = storecard::create([
                        'user_id' => $user_id,
                        'st_id' => $store[0]['id'],
                        'cardname' => $card_data[0]['cardname'],
                        'cardno' => $card_data[0]['cardno'],
                        'rewardpercen' => $card_data[0]['rewardpercen'],
                        'carddetail' => $card_data[0]['carddetail'],
                        'expdate' => $card_data[0]['expdate'],
                        'card_img' => $card_data[0]['card_img'],
                    ]);
                    $success['st_id'] = $data['st_id'];
                    $success['cardname'] = $data['cardname'];
                    $success['cardno'] = $data['cardno'];
                    $success['rewardpercen'] = $data['rewardpercen'];
                    $success['carddetail'] = $data['carddetail'];
                    $success['expdate'] = $data['expdate'];
                    $success['card_img'] = $data['card_img'];

                    return $this->sendResponse(
                        $success,
                        'Shared Card Added Successfully.',
                        ['Error' => 'Operation Failed']
                    );
                }
                $fail['cardname'] = $available[0]['cardname'];
                $fail['cardno'] = $available[0]['cardno'];
                return $this->sendError('Card is Already Exist', $fail);
            }
            return $this->sendError(
                'Store ' .
                    $storedata[0]['stname'] .
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
