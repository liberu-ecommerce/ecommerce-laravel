<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * GDPR right-to-erasure (Art. 17). Anonymises a user rather than hard-deleting them so
 * their orders remain intact for accounting/legal retention while every identifying
 * field is scrubbed. All writes happen in one transaction.
 *
 * ponytail: scrubs the core identity, order PII, saved payment methods and behavioural
 * tracking. User-generated content (reviews/ratings) and gift registries are left for a
 * follow-up — deleting them changes public product aggregates and needs its own call.
 */
class GdprErasureService
{
    private const REDACTED_EMAIL = 'redacted@anonymized.invalid';

    private const REDACTED = 'REDACTED';

    public function erase(User $user): void
    {
        DB::transaction(function () use ($user) {
            $customer = $user->customer;

            $this->scrubOrders($user, $customer?->id);

            // Personal data with no accounting value — delete outright.
            $user->paymentMethods()->delete();
            $user->browsingHistory()->delete();
            $user->productInteractions()->delete();
            $user->wishlist()->delete();

            if ($customer !== null) {
                $customer->update([
                    'first_name' => 'Deleted',
                    'last_name' => 'User',
                    'email' => self::REDACTED_EMAIL,
                    'phone_number' => null,
                    'address' => null,
                    'city' => null,
                    'state' => null,
                    'postal_code' => null,
                ]);
            }

            // Anonymise the account in place (row kept so orders keep their owner link).
            $user->forceFill([
                'name' => 'Deleted User',
                'email' => 'deleted-'.$user->id.'@anonymized.invalid',
                'email_verified_at' => null,
                'password' => Hash::make(Str::random(64)),
                'remember_token' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
            ])->save();
        });
    }

    private function scrubOrders(User $user, ?int $customerId): void
    {
        $query = Order::query()->where('user_id', $user->id);
        if ($customerId !== null) {
            $query->orWhere('customer_id', $customerId);
        }

        $query->update([
            'customer_email' => self::REDACTED_EMAIL,
            'shipping_address' => self::REDACTED,
            'recipient_name' => self::REDACTED,
            'recipient_email' => self::REDACTED_EMAIL,
            'gift_message' => null,
        ]);
    }
}
