<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            Category::query()->whereNull('parent_id')->with('children')->orderBy('sort_order')->get()
        );
    }
}
