<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CollectionController extends Controller
{
    /**
     * Display a listing of collections.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = QueryBuilder::for(ProductCollection::class)
            ->allowedFilters([
                'name',
                AllowedFilter::exact('id'),
            ])
            ->allowedSorts(['name', 'created_at', 'updated_at'])
            ->with('products');

        // Check if pagination is requested
        if ($request->has('per_page')) {
            $perPage = min((int) $request->input('per_page', 15), 100);
            $collections = $query->paginate($perPage);
        } else {
            $collections = $query->get();
        }

        return response()->json($collections);
    }

    /**
     * Store a newly created collection.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:collections,slug',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $collection = ProductCollection::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Collection created successfully',
            'data' => $collection
        ], 201);
    }

    /**
     * Display the specified collection.
     *
     * @param string $idOrSlug
     * @return JsonResponse
     */
    public function show(string $idOrSlug): JsonResponse
    {
        // Try to find by ID first, then by slug
        $collection = is_numeric($idOrSlug)
            ? ProductCollection::with('products')->find($idOrSlug)
            : ProductCollection::with('products')->where('slug', $idOrSlug)->first();

        if (!$collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $collection
        ]);
    }

    /**
     * Update the specified collection.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $collection = ProductCollection::find($id);

        if (!$collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:collections,slug,' . $id,
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        
        // Auto-generate slug if name is updated but slug is not provided
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $collection->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Collection updated successfully',
            'data' => $collection->fresh()
        ]);
    }

    /**
     * Add products to a collection.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function addProducts(Request $request, int $id): JsonResponse
    {
        $collection = ProductCollection::find($id);

        if (!$collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|integer|exists:products,id',
            'quantities' => 'sometimes|array',
            'quantities.*' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $productIds = $request->input('product_ids');
        $quantities = $request->input('quantities', []);

        // Prepare sync data with quantities
        $syncData = [];
        foreach ($productIds as $index => $productId) {
            $syncData[$productId] = [
                'quantity' => $quantities[$index] ?? 1
            ];
        }

        // Attach products without detaching existing ones
        $collection->products()->syncWithoutDetaching($syncData);

        return response()->json([
            'success' => true,
            'message' => 'Products added to collection successfully',
            'data' => $collection->load('products')
        ]);
    }

    /**
     * Remove products from a collection.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function removeProducts(Request $request, int $id): JsonResponse
    {
        $collection = ProductCollection::find($id);

        if (!$collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $productIds = $request->input('product_ids');

        // Detach specified products
        $collection->products()->detach($productIds);

        return response()->json([
            'success' => true,
            'message' => 'Products removed from collection successfully',
            'data' => $collection->load('products')
        ]);
    }

    /**
     * Soft delete the specified collection.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $collection = ProductCollection::find($id);

        if (!$collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);
        }

        $collection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Collection deleted successfully'
        ]);
    }
}
