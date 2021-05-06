<?php

namespace App\Http\Controllers;

use App\storecard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use File;

class StoreCardController extends Controller
{
    public function store_card(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cardname' => 'required',
                'rewardpercen' => 'required',
                'carddetail' => 'required',
                'expdate' => 'required',
                'card_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    'Validator Error.',
                    $validator->errors()
                );
            }

            $image = $request->file('card_img');
            $new_name = rand() . '.' . $image->getClientOriginalExtension();
            $path = public_path() . '/upload/card_img';
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $image->move(public_path('/upload/card_img'), $new_name);
            $path = 'http://stocard.project-demo.info/upload/card_img/';
            $path .= $new_name;

            $input = $request->all();
            $input['user_id'] = Auth::guard('api')->user()->id;
            $input['card_img'] = $path;
            $user = storecard::create($input);

            $success['Card_Name'] = $user->cardname;
            $success['Card_Reward_Per'] = $user->rewardpercen;
            $success['Card_no'] = $user->cardno;
            $success['Card_detail'] = $user->carddetail;
            $success['Image'] = $user->card_img;
            $success['Card_Expdate'] = $user->expdate;

            return $this->sendResponse($success, 'Card Added Successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function card_details(Request $request)
    {
        try {
            $id = Auth::guard('api')->user()->id;
            $aa = $request->st_id;
            $user = DB::Table('storecards')
                ->select(
                    'id',
                    'cardname',
                    'rewardpercen',
                    'cardno',
                    'expdate',
                    'carddetail',
                    'card_img'
                )
                ->where('user_id', $id)
                ->where('st_id', $aa)
                ->get();

            return $this->sendResponse($user, 'Selected Card Detail');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->sendMessage());
        }
    }

    public function card_delete(Request $request)
    {
        $id = $request->id;
        $res = storecard::where('id', $id)->delete();
        if ($res) {
            $data = [
                'status' => true,
                'message' => 'Deleted Successfully',
            ];
        } else {
            $data = [
                'status' => false,
                'message' => $res,
            ];
        }
        return response()->json($data);
    }
}
