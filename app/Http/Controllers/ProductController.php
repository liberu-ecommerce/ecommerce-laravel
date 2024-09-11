<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\BrowsingHistory;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

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

    public function show($category, Product $product)
    {
        // $product = Product::where('id', $product)->firstOrFail();

        // Track browsing history
        if (auth()->check()) {
            BrowsingHistory::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);
        }

        // Get recommendations
        $recommendations = [];
        if (auth()->check()) {
            $recommendations = $this->recommendationService->getRecommendations(auth()->user());
        }

        $metaTitle = $product->meta_title ?? $product->name;
        $metaDescription = $product->meta_description ?? $product->short_description;
        $metaKeywords = $product->meta_keywords;
        $canonicalUrl = route('products.show', ['category' => $category, 'product' => $product->slug]);

        return view('products.show', compact('product', 'recommendations', 'metaTitle', 'metaDescription', 'metaKeywords', 'canonicalUrl'));
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

    public function search(Request $request)
    {
        $query = Product::query();

        if ($request->has('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('description', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->paginate(12);

        return view('products.search', compact('products'));
    }

    public function addToCompare(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $compareList = Session::get('compare_list', []);

        if (!in_array($id, $compareList) && count($compareList) < 4) {
            $compareList[] = $id;
            Session::put('compare_list', $compareList);
            return redirect()->back()->with('success', 'Product added to comparison.');
        } elseif (in_array($id, $compareList)) {
            return redirect()->back()->with('info', 'Product is already in the comparison list.');
        } else {
            return redirect()->back()->with('error', 'You can compare up to 4 products at a time.');
        }
    }

    public function compare()
    {
        $compareList = Session::get('compare_list', []);
        $products = Product::whereIn('id', $compareList)->get();

        return view('products.compare', compact('products'));
    }

    public function removeFromCompare($id)
    {
        $compareList = Session::get('compare_list', []);
        $compareList = array_diff($compareList, [$id]);
        Session::put('compare_list', $compareList);

        return redirect()->back()->with('success', 'Product removed from comparison.');
    }

    public function clearCompare()
    {
        Session::forget('compare_list');
        return redirect()->route('products.list')->with('success', 'Comparison list cleared.');
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
