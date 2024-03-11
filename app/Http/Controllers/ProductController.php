<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string|max:255',
            'inventory_count' => 'required|integer',
        ]);

        $product = Product::create($validatedData);

        return response()->json($product, Response::HTTP_CREATED);
    }

    public function list()
    {
        $products = Product::all();

        return response()->json($products);
    }

    public function show($id)
    /**
     * List all products.
     * 
     * @return \Illuminate\Http\Response
     */
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'name' => 'string|max:255',
/**
 * This file contains the ProductController class responsible for handling HTTP requests related to products,
 * such as creating, listing, showing, updating, and deleting products.
 */
    /**
     * Create a new product instance after a valid request.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
            'description' => 'string',
            'price' => 'numeric',
            'category' => 'string|max:255',
            'inventory_count' => 'integer',
        ]);

        $product->update($validatedData);

        return response()->json($product);
    }

    public function delete($id)
    /**
     * Update the specified product in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
    /**
     * Display the specified product.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Remove the specified product from storage.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
