<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Notifications\PaypalTransactionNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

class PaypalTransactionNotificationTest extends TestCase
{
    public function testNotificationChannels()
    {
        $notification = new PaypalTransactionNotification(['type' => 'test', 'amount' => 100]);
        $channels = $notification->via(new AnonymousNotifiable);
        $this->assertEquals(['mail', 'database'], $channels);
    }

    public function testMailMessageForSubscriptionRenewal()
    {
        Notification::fake();
        $notification = new PaypalTransactionNotification(['type' => 'subscription_renewal', 'amount' => 100]);
        $notification->toMail(new AnonymousNotifiable);
        Notification::assertSentTo(new AnonymousNotifiable, PaypalTransactionNotification::class, function ($notification, $channels) {
            return str_contains($notification->toMail(new AnonymousNotifiable)->render(), 'Your subscription has been successfully renewed.');
        });
    }

    public function testMailMessageForUpcomingCharge()
    {
        Notification::fake();
        $notification = new PaypalTransactionNotification(['type' => 'upcoming_charge', 'amount' => 200]);
        $notification->toMail(new AnonymousNotifiable);
        Notification::assertSentTo(new AnonymousNotifiable, PaypalTransactionNotification::class, function ($notification, $channels) {
            return str_contains($notification->toMail(new AnonymousNotifiable)->render(), 'You have an upcoming charge for your subscription.');
        });
    }

    public function testMailMessageForSubscriptionCancellation()
    {
        Notification::fake();
        $notification = new PaypalTransactionNotification(['type' => 'subscription_cancellation', 'amount' => 300]);
        $notification->toMail(new AnonymousNotifiable);
        Notification::assertSentTo(new AnonymousNotifiable, PaypalTransactionNotification::class, function ($notification, $channels) {
            return str_contains($notification->toMail(new AnonymousNotifiable)->render(), 'Your subscription has been cancelled.');
        });
    }

    public function testMailMessageForDefaultCase()
    {
        Notification::fake();
        $notification = new PaypalTransactionNotification(['type' => 'other', 'amount' => 400]);
        $notification->toMail(new AnonymousNotifiable);
        Notification::assertSentTo(new AnonymousNotifiable, PaypalTransactionNotification::class, function ($notification, $channels) {
            return str_contains($notification->toMail(new AnonymousNotifiable)->render(), 'Your payment was successful.');
        });
    }
}
