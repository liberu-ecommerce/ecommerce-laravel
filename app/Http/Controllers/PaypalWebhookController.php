<?php

namespace App\Http\Controllers;

use App\Models\PaypalSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalWebhookController extends Controller
{
    /**
     * Local (crypto) status → target: only the subscription lifecycle events that
     * change our stored status. Everything else is acknowledged and ignored.
     */
    private const STATUS_MAP = [
        'BILLING.SUBSCRIPTION.ACTIVATED' => 'ACTIVE',
        'BILLING.SUBSCRIPTION.CANCELLED' => 'CANCELLED',
        'BILLING.SUBSCRIPTION.SUSPENDED' => 'SUSPENDED',
        'BILLING.SUBSCRIPTION.EXPIRED' => 'EXPIRED',
    ];

    /**
     * Signature-verified PayPal webhook. PayPal — not our synchronous create call —
     * is the source of truth for when a subscription activates/cancels/expires, so we
     * reconcile the local status here. Unauthenticated + CSRF-exempt: the signature is
     * the only gate. Verification is fully local (RSA-SHA256 against PayPal's cert), so
     * no API credentials or round-trip are needed.
     */
    public function handle(Request $request, PayPalClient $paypal): JsonResponse
    {
        $webhookId = (string) config('paypal.webhook_id');

        $verified = $webhookId !== '' && $paypal->verifyWebHookLocally(
            $this->flatHeaders($request),
            $webhookId,
            $request->getContent(),
        );

        if (! $verified) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $status = self::STATUS_MAP[$request->input('event_type')] ?? null;
        $subscriptionId = $request->input('resource.id');

        // Unmapped event or event for a subscription we don't track: ack and ignore.
        // update() is a no-op on zero matches, and setting an absolute status keeps
        // this idempotent across PayPal's at-least-once webhook retries.
        if ($status !== null && $subscriptionId !== null) {
            PaypalSubscription::where('paypal_subscription_id', $subscriptionId)
                ->update(['status' => $status]);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Collapse Laravel's array-valued headers to the scalar strings
     * verifyWebHookLocally expects (it reads each PAYPAL-* header as a string).
     */
    private function flatHeaders(Request $request): array
    {
        return array_map(
            fn ($value) => is_array($value) ? ($value[0] ?? '') : $value,
            $request->headers->all(),
        );
    }
}
