&lt;?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['name', 'value', 'description'];

    protected $casts = [
        'value' => 'string',
    ];
}
