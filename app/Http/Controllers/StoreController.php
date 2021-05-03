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
                'stcontact' => 'required',
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

            $user = DB::Table('storedatas')
                ->select('id', 'stname', 'stlocation', 'stcontact', 'store_img')
                ->where('user_id', $id)
                ->get();

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
                ->select('id', 'stname', 'user_id', 'stlocation', 'stcontact')
                ->where('user_id', $id)
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
}
