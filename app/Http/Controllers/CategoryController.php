<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    public function fetchall(Request $request)
    {
        $data = Category::all();
        $category = CategoryResource::collection($data);
        return $this->sendResponse(
            'All Category Fetch Successfully.',
            $category
        );
    }
}
