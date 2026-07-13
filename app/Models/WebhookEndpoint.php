<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'secret', 'events', 'is_active'];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];

    /** Does this endpoint subscribe to the given event name? */
    public function subscribesTo(string $event): bool
    {
        return in_array($event, (array) $this->events, true);
    }
}
