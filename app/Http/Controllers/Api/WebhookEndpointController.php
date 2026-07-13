<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookEndpointController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        return response()->json(WebhookEndpoint::latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'url' => 'required|url',
            'events' => 'required|array|min:1',
            'events.*' => 'required|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // The signing secret is generated server-side and returned once, so the
        // receiver can verify the X-Webhook-Signature.
        $endpoint = WebhookEndpoint::create([
            'url' => $validated['url'],
            'events' => $validated['events'],
            'is_active' => $validated['is_active'] ?? true,
            'secret' => 'whsec_'.Str::random(40),
        ]);

        return response()->json($endpoint, 201);
    }

    public function update(Request $request, WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'url' => 'sometimes|url',
            'events' => 'sometimes|array|min:1',
            'events.*' => 'required|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $webhookEndpoint->update($validated);

        return response()->json($webhookEndpoint->fresh());
    }

    public function destroy(Request $request, WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $this->authorizeStaff($request);

        $webhookEndpoint->delete();

        return response()->json(['message' => 'Webhook endpoint deleted.']);
    }

    private function authorizeStaff(Request $request): void
    {
        abort_unless($request->user()?->hasRole(['super_admin', 'admin']), 403);
    }
}
