<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bundle;

class BundleController extends Controller
{
    public function index()
    {
        return response()->json(
            Bundle::active()
                ->with(['vendor:id,store_name', 'products:id,title,slug,thumbnail_url,base_price'])
                ->latest()
                ->get()
        );
    }

    public function show(Bundle $bundle)
    {
        abort_unless($bundle->is_active, 404);

        $bundle->load(['vendor', 'products.category']);

        return response()->json($bundle);
    }
}
