<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:product_categories,id',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku'
        ]);

        $product = Product::create($request->all());
        return response()->json($product, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $this->validate($request, [
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'category_id' => 'exists:product_categories,id',
            'stock_quantity' => 'integer|min:0',
            'sku' => 'string|unique:products,sku,' . $id
        ]);

        $product->update($request->all());
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function updateStock(Request $request, $id)
    {
        $this->validate($request, [
            'quantity' => 'required|integer'
        ]);

        $product = Product::findOrFail($id);
        $product->stock_quantity += $request->quantity;
        $product->save();

        return response()->json($product);
    }

    public function getByCategory($categoryId)
    {
        $products = Product::where('category_id', $categoryId)->get();
        return response()->json($products);
    }
} 