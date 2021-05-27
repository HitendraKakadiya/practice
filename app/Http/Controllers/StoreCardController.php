<?php

namespace App\Http\Controllers;

use App\storecard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use File;
use Carbon\Carbon;

class StoreCardController extends Controller
{
    public function store_card(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cardname' => 'required',
                'rewardpercen' => 'required|min:1|max:100|numeric',
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
            $input['status'] = 'show';
            $input['isActive'] = 'true';
            $user = storecard::create($input);

            $success['Card_Name'] = $user->cardname;
            $success['Card_Reward_Per'] = $user->rewardpercen;
            $success['Card_no'] = $user->cardno;
            $success['Card_detail'] = $user->carddetail;
            $success['Image'] = $user->card_img;
            $success['Card_Expdate'] = $user->expdate;
            $success['status'] = $user['status'];
            $success['isActive'] = $user['isActive'];

            return $this->sendResponse('Card Added Successfully', $success);
        } catch (\Exception $e) {
            return $this->sendError('Error', 'Something want Wrong!!!');
        }
    }

    public function card_details(Request $request)
    {
        try {
            $date1 = Carbon::now()->toDateString();
            $card = storecard::where('isActive', 'true')->get();
            foreach ($card as $item) {
                $date2 = $item['expdate'];
                if ($date1 >= $date2) {
                    storecard::where('expdate', $date2)->update([
                        'isActive' => 'false',
                    ]);
                }
            }

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
                    'card_img',
                    'status',
                    'isActive'
                )
                ->where('user_id', $id)
                ->where('st_id', $aa)
                ->orderBy('cardname')
                ->get();

            return $this->sendResponse('Selected Card Detail', $user);
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
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

    public function hide_card(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validator Error.', $validator->errors());
        }
        $card_id = $request->input('card_id');
        $card_data = storecard::where('id', $card_id)->first();
        if ($card_data['status'] == 'show') {
            $update = storecard::where('id', $card_id)->update([
                'status' => 'hide',
            ]);
            $success['id'] = $card_data['id'];
            $success['status'] = $card_data['status'];

            if ($update) {
                return $this->sendResponse('Card Lock Successfully.', $success);
            } else {
                return $this->sendError(
                    'Card does not Lock because of some Error.',
                    ['Error' => 'Operation Failed']
                );
            }
        } else {
            $success['id'] = $card_data['id'];
            $success['status'] = $card_data['status'];
            return $this->sendError(
                'Card already Lock you can not hide it again!!!',
                $success
            );
        }
    }

    public function show_card(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validator Error.', $validator->errors());
        }
        $card_id = $request->input('card_id');
        $card_data = storecard::where('id', $card_id)->first();
        if (!empty($card_data)) {
            if ($card_data['status'] == 'hide') {
                $update = storecard::where('id', $card_id)->update([
                    'status' => 'show',
                ]);
                $card_data = storecard::where('id', $card_id)->first();
                $success['id'] = $card_data['id'];
                $success['status'] = $card_data['status'];

                if ($update) {
                    return $this->sendResponse(
                        'Card Un-Lock Successfully.',
                        $success
                    );
                } else {
                    return $this->sendError(
                        'Card does not Un-Lock because of some Error.',
                        ['Error' => 'Operation Failed']
                    );
                }
            } else {
                $success['id'] = $card_data['id'];
                $success['status'] = $card_data['status'];
                return $this->sendError(
                    'Card already in Un-Lock mode!!!',
                    $success
                );
            }
        }
        return $this->sendError(
            'Card ' . $card_id . ' Does not Exist. Please Check Again!!!',
            [
                'Error' => 'Operation Failed',
            ]
        );
    }
}
