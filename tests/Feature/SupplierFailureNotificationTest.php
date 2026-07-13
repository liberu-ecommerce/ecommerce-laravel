<?php

namespace Tests\Feature;

use App\Notifications\SupplierFailureNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SupplierFailureNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every caller sends this notification to an AnonymousNotifiable via
     * Notification::route('mail', ...). If via() also lists the 'database' channel,
     * DatabaseChannel calls ->create() on the null 'database' route and throws a
     * \Error on every supplier failure — a normal dropship outcome — which is not
     * caught by the callers' catch(Exception) and 500s checkout after payment.
     */
    public function test_it_delivers_to_a_mail_only_notifiable_without_fataling(): void
    {
        // Production sets MAIL_FROM_ADDRESS; the test env doesn't, so set it here to
        // exercise the real mail channel (array transport) end to end.
        config(['mail.from.address' => 'store@example.com', 'mail.from.name' => 'Store']);

        Notification::route('mail', 'ops@example.com')
            ->notify(new SupplierFailureNotification('Supplier rejected order 1'));

        // Reaching this line means no channel fataled on the anonymous notifiable.
        $this->assertTrue(true);
    }

    public function test_via_does_not_include_database_for_an_anonymous_notifiable(): void
    {
        $channels = (new SupplierFailureNotification('x'))->via(
            Notification::route('mail', 'ops@example.com')
        );

        $this->assertNotContains('database', $channels);
    }
}
