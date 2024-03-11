&lt;?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    protected $table = 'inventory_logs';

    protected $fillable = [
        'product_id',
        'quantity_change',
        'reason',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
