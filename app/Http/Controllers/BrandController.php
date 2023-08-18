<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function create(Request $request){

        $validated = $request->validate([
            'name'          => 'required|max:255',
            'image_link'    => 'nullable|text',
        ]);
    }
}
