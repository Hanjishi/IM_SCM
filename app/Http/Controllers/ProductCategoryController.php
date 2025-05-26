<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::with('products')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:product_categories,name',
            'description' => 'required|string'
        ]);

        $category = ProductCategory::create($request->all());
        return response()->json($category, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $category = ProductCategory::with('products')->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);
        
        $this->validate($request, [
            'name' => 'string|max:255|unique:product_categories,name,' . $id,
            'description' => 'string'
        ]);

        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
} 