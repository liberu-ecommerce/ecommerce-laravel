&lt;?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = ['user_id', 'name', 'details', 'is_default'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
