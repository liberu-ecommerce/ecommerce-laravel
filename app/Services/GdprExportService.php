<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;

/**
 * Assembles a user's personal data for a GDPR right-of-access / portability export.
 *
 * Every entity is field-whitelisted rather than dumped: credentials, 2FA material and
 * raw payment-method `details` must never leave the system, and a future column added
 * to one of these tables must not silently start appearing in exports.
 *
 * ponytail: covers the core identity + transactional data. Behavioural/tracking data
 * (browsing history, product interactions, customer segments) is personal data too —
 * add it here when a user actually asks for it; it is high-volume, derived, and not
 * needed for the first slice.
 */
class GdprExportService
{
    public function export(User $user): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => optional($user->email_verified_at)->toIso8601String(),
                'created_at' => optional($user->created_at)->toIso8601String(),
            ],
            'customer' => $this->customer($user),
            'orders' => $this->orders($user),
            'payment_methods' => $user->paymentMethods->map(fn ($pm) => [
                'name' => $pm->name,
                'is_default' => (bool) $pm->is_default,
            ])->all(),
        ];
    }

    private function customer(User $user): ?array
    {
        $customer = $user->customer;
        if ($customer === null) {
            return null;
        }

        return [
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'email' => $customer->email,
            'phone_number' => $customer->phone_number,
            'address' => $customer->address,
            'city' => $customer->city,
            'state' => $customer->state,
            'postal_code' => $customer->postal_code,
        ];
    }

    private function orders(User $user): array
    {
        $query = Order::query()->where('user_id', $user->id);
        if ($user->customer !== null) {
            $query->orWhere('customer_id', $user->customer->id);
        }

        return $query->latest('id')->get()->map(fn (Order $o) => [
            'id' => $o->id,
            'order_date' => $o->order_date,
            'total_amount' => $o->total_amount,
            'tax_amount' => $o->tax_amount,
            'shipping_cost' => $o->shipping_cost,
            'discount_amount' => $o->discount_amount,
            'coupon_code' => $o->coupon_code,
            'payment_status' => $o->payment_status,
            'shipping_status' => $o->shipping_status,
            'status' => $o->status,
        ])->all();
    }
}
