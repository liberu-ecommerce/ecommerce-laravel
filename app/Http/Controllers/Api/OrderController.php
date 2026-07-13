<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Paginated list of the authenticated user's own orders, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->latest()
            ->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * A single order owned by the authenticated user. Scoping by user_id is the
     * IDOR guard — another user's (or a guest's) order is simply not found.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with(['items', 'statusHistory'])
            ->find($id);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $order]);
    }
}
