&lt;?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadableProduct extends Model
{
    protected $fillable = [
        'product_id',
        'file_url',
        'download_limit',
        'expiration_time',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
