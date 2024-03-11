<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function create(Request $request)
    {
        // Handle Product File Upload
        if ($request->hasFile('product_file')) {
            $file = $request->file('product_file');
            $filePath = $file->store('public/downloadable_products');
            $fileUrl = Storage::url($filePath);
        } else {
            $fileUrl = null;
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string|max:255',
            'inventory_count' => 'required|integer',
            // Include download limit in validation
            'download_limit' => 'integer|nullable',
        ]);

        $product = Product::create($validatedData);
        
        // Create an initial inventory log entry
        $product->inventoryLogs()->create([
            'quantity_change' => $validatedData['inventory_count'],
            'reason' => 'Initial stock setup',
        ]);

        return response()->json($product, Response::HTTP_CREATED);
    }

    public function list()
    {
        $products = Product::all();

        return response()->json($products);
    }

    public function show($id)
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
            'description' => 'string',
            'price' => 'numeric',
            'category' => 'string|max:255',
            'inventory_count' => 'integer',
        ]);

        // Handle Product File Upload for Update
        if ($request->hasFile('product_file')) {
            $file = $request->file('product_file');
            $filePath = $file->store('public/downloadable_products');
            $fileUrl = Storage::url($filePath);
            // Update Downloadable Product entry
            $product->downloadable()->updateOrCreate(['product_id' => $product->id], ['file_url' => $fileUrl, 'download_limit' => $request->download_limit]);
        }

        $product->update($validatedData);

        return response()->json($product);
    }

    public function delete($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
        // Check if inventory_count is being updated and log the change
        if (isset($validatedData['inventory_count'])) {
            $quantityChange = $validatedData['inventory_count'] - $product->getOriginal('inventory_count');
            $product->inventoryLogs()->create([
                'quantity_change' => $quantityChange,
                'reason' => 'Inventory adjustment',
            ]);
        }
