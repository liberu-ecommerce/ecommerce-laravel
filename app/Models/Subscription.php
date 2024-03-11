&lt;?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

class Subscription extends Model
{
    use Billable;

    protected $fillable = [
        'name', 'stripe_id', 'stripe_status', 'stripe_plan', 'quantity', 'trial_ends_at', 'ends_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->stripe_status === 'active';
    }

    public function cancel()
    {
        $this->subscription('default')->cancel();
    }

    public function renew()
    {
        if ($this->onGracePeriod()) {
            $this->subscription('default')->resume();
        } else {
            // Handle logic for subscriptions that are not in grace period
        }
    }
}
