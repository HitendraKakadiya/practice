<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\storedata;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use File;

class StoreController extends Controller
{
    public function add_store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'stname' => 'required',
                'stlocation' => 'required',
                'stcontact' => 'required|min:10|max:10',
                'store_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    'Validator Error.',
                    $validator->errors()
                );
            }

            $image = $request->file('store_img');
            $new_name = rand() . '.' . $image->getClientOriginalExtension();
            $path = public_path() . '/upload/store_img';
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $image->move(public_path('/upload/store_img'), $new_name);
            $path = 'http://stocard.project-demo.info/upload/store_img/';
            $path .= $new_name;

            $input = $request->all();
            $input['store_img'] = $path;
            $input['user_id'] = Auth::guard('api')->user()->id;
            $user = storedata::create($input);
            $success['stname'] = $user->stname;
            $success['stlocation'] = $user->stlocation;
            $success['stcontact'] = $user->stcontact;
            $success['Image'] = $user->store_img;

            return $this->sendResponse($success, 'Store Add Successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function store_listing()
    {
        try {
            $id = Auth::guard('api')->user()->id;

            $favorite = DB::Table('storedatas')
                ->select(
                    'id',
                    'stname',
                    'stlocation',
                    'stcontact',
                    'store_img',
                    'is_favorite'
                )
                ->where('user_id', $id)
                ->where('is_favorite', 'true')
                ->orderBy('stname')
                ->get();

            $un_favorite = DB::Table('storedatas')
                ->select(
                    'id',
                    'stname',
                    'stlocation',
                    'stcontact',
                    'store_img',
                    'is_favorite'
                )
                ->where('user_id', $id)
                ->where('is_favorite', 'false')
                ->orderBy('stname')
                ->get();
            $merged = $favorite->merge($un_favorite);
            $user = $merged->all();

            return $this->sendResponse($user, 'List of Stores');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function store_details(Request $request)
    {
        try {
            $id = Auth::guard('api')->user()->id;
            $stid = $request->stid;
            $data = DB::table('storedatas')
                ->select(
                    'id',
                    'stname',
                    'user_id',
                    'stlocation',
                    'stcontact',
                    'is_favorite'
                )
                ->where('user_id', $id)
                ->orderBy('stname')
                ->get();

            return $this->sendResponse($data, 'Selected Store Data');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function store_delete(Request $request)
    {
        $id = $request->id;
        $res = storedata::where('id', $id)->delete();

        if ($res) {
            $data = [
                'status' => true,
                'message' => 'Deleted Successfully',
            ];
        } else {
            $data = [
                'status' => false,
                'message' => 'Failed',
            ];
        }
        return response()->json($data);
    }
    public function add_favorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validator Error.', $validator->errors());
        }
        $store_id = $request->input('store_id');
        $store_data = storedata::where('id', $store_id)->get();
        if ($store_data[0]['is_favorite'] == 'false') {
            $update = storedata::where('id', $store_id)->update([
                'is_favorite' => 'true',
            ]);
            $store_data = storedata::where('id', $store_id)->get();
            $success['id'] = $store_data[0]['id'];
            $success['is_favorite'] = $store_data[0]['is_favorite'];

            if ($update) {
                return $this->sendResponse(
                    $success,
                    'Store Add to Favorite Successfully.'
                );
            } else {
                return $this->sendError(
                    'Store does not add to Favorite because of some Error.',
                    ['Error' => 'Operation Failed']
                );
            }
        } else {
            $success['id'] = $store_data[0]['id'];
            $success['is_favorite'] = $store_data[0]['is_favorite'];
            return $this->sendError(
                'Store already in Favorite, you can not add it again!!!',
                $success
            );
        }
    }

    public function remove_favorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validator Error.', $validator->errors());
        }
        $store_id = $request->input('store_id');
        $store_data = storedata::where('id', $store_id)->get();
        if (!$store_data->isEmpty()) {
            if ($store_data[0]['is_favorite'] == 'true') {
                $update = storedata::where('id', $store_id)->update([
                    'is_favorite' => 'false',
                ]);
                $store_data = storedata::where('id', $store_id)->get();
                $success['id'] = $store_data[0]['id'];
                $success['is_favorite'] = $store_data[0]['is_favorite'];

                if ($update) {
                    return $this->sendResponse(
                        $success,
                        'Store Remove from Favorite Successfully.'
                    );
                } else {
                    return $this->sendError(
                        'Store does not remove from favorite because of some Error.',
                        ['Error' => 'Operation Failed']
                    );
                }
            } else {
                $success['id'] = $store_data[0]['id'];
                $success['is_favorite'] = $store_data[0]['is_favorite'];
                return $this->sendError(
                    'Store already in favorite mode!!!',
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
