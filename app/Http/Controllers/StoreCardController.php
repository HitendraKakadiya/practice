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

            return $this->sendResponse($success, 'Card Added Successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
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

            return $this->sendResponse($user, 'Selected Card Detail');
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
        $card_data = storecard::where('id', $card_id)->get();
        if ($card_data[0]['status'] == 'show') {
            $update = storecard::where('id', $card_id)->update([
                'status' => 'hide',
            ]);
            $success['id'] = $card_data[0]['id'];
            $success['status'] = $card_data[0]['status'];

            if ($update) {
                return $this->sendResponse($success, 'Card Hide Successfully.');
            } else {
                return $this->sendError(
                    'Card does not Hide because of some Error.',
                    ['Error' => 'Operation Failed']
                );
            }
        } else {
            $success['id'] = $card_data[0]['id'];
            $success['status'] = $card_data[0]['status'];
            return $this->sendError(
                'Card already hide you can not hide it again!!!',
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
        $card_data = storecard::where('id', $card_id)->get();
        if (!$card_data->isEmpty()) {
            if ($card_data[0]['status'] == 'hide') {
                $update = storecard::where('id', $card_id)->update([
                    'status' => 'show',
                ]);
                $card_data = storecard::where('id', $card_id)->get();
                $success['id'] = $card_data[0]['id'];
                $success['status'] = $card_data[0]['status'];

                if ($update) {
                    return $this->sendResponse(
                        $success,
                        'Card Show Successfully.'
                    );
                } else {
                    return $this->sendError(
                        'Card does not Show because of some Error.',
                        ['Error' => 'Operation Failed']
                    );
                }
            } else {
                $success['id'] = $card_data[0]['id'];
                $success['status'] = $card_data[0]['status'];
                return $this->sendError(
                    'Card already in Show mode!!!',
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

        //     if ($validator->fails()) {
        //         return $this->sendError('Validator Error.', $validator->errors());
        //     }
        //     $update = storecard::where('status', 'hide')->update([
        //         'status' => 'show',
        //     ]);
        //     $success = $update . ' record Updated';
        //     if ($update) {
        //         return $this->sendResponse($success, 'Card Show Successfully.');
        //     } else {
        //         return $this->sendError('All card Already in Show mode.', [
        //             'Error' => 'Operation Failed',
        //         ]);
        //     }
    }
}
